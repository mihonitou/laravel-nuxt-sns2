<script setup lang="ts">
import { ref } from 'vue'
import { useForm, useField } from 'vee-validate'
import * as yup from 'yup'
import { useRouter } from 'vue-router'
import { getAuth, createUserWithEmailAndPassword, updateProfile } from "firebase/auth"
import { useAuth } from '~/composables/useAuth'


const schema = yup.object({
  username: yup
    .string()
    .required('ユーザーネームを入力してください')
    .max(20, '20文字以内で入力してください'),

  email: yup
    .string()
    .required('メールアドレスを入力してください')
    .email('メールアドレス形式で入力してください'),

  password: yup
    .string()
    .required('パスワードを入力してください')
    .min(6, '6文字以上で入力してください')
})

const { handleSubmit } = useForm({
  validationSchema: schema
})

const { value: username, errorMessage: usernameError }
  = useField<string>('username')

const { value: email, errorMessage: emailError }
  = useField<string>('email')

const { value: password, errorMessage: passwordError }
  = useField<string>('password')

const { setUser } = useAuth()
const router = useRouter()

const register = handleSubmit(async () => {
  try {
    const auth = getAuth()

    // 🔥 Firebaseにユーザー作成
    const userCredential = await createUserWithEmailAndPassword(
      auth,
      email.value,
      password.value
    )

    const firebaseUser = userCredential.user
    if (!firebaseUser) throw new Error("User not found")

    // 🔥 displayNameセット
    await updateProfile(firebaseUser, {
      displayName: username.value,
    })

    // 🔥 IDトークン取得
    const idToken = await firebaseUser.getIdToken(true)

    localStorage.setItem('idToken', idToken)

    // 🔥 Laravelに登録（ログインと同じAPIでOK）
    await $fetch("http://localhost/api/firebase-login", {
      method: "POST",
      body: { idToken }
    })

    // 🔥 /me取得
    const me: any = await $fetch("http://localhost/api/me", {
      method: "POST",
      headers: {
        Authorization: `Bearer ${idToken}`
      }
    })

    setUser(me.user)

    await router.push('/')

    alert('登録＆ログイン成功！')

  } catch (error: any) {
    console.error("❌ 登録エラー", error)
    alert('登録に失敗しました: ' + error.message)
  }
})

</script>

<template>
  <div class="register-page">
    <!-- 共通ヘッダー -->
    <AuthHeader />

    <!-- 登録フォーム -->
    <div class="form-container">
      <h2>新規登録</h2>

      <!-- ユーザー名 -->
      <div class="form-group">
        <input
          v-model="username"
          type="text"
          placeholder="ユーザーネーム"
        />
      <p
        v-if="usernameError"
        class="error-message"
      >
        {{ usernameError }}
      </p>
      </div>

      <!-- メールアドレス -->
      <div class="form-group">
        <input
          v-model="email"
          type="email"
          placeholder="メールアドレス"
        />
      <p
        v-if="emailError"
        class="error-message"
      >
        {{ emailError }}
      </p>
      </div>

      <!-- パスワード -->
      <div class="form-group">
        <input
          v-model="password"
          type="password"
          placeholder="パスワード"
        />
      <p
        v-if="passwordError"
        class="error-message"
      >
        {{ passwordError }}
      </p>
      </div>

      <button @click="register">新規登録</button>
    </div>
  </div>
</template>

<style scoped>
.register-page {
  min-height: 100vh;
  color: white;
  display: flex;
  flex-direction: column;
}

.form-container {
  background: white;
  color: black;
  border-radius: 12px;
  padding: 2rem;
  margin: 3rem auto;
  width: 300px;
  text-align: center;
}

.form-group {
  margin-bottom: 1rem;
}

input {
  display: block;
  width: 100%;
  padding: 0.6rem;
  border-radius: 8px;
  border: 1px solid #ccc;
}

button {
  background: #5b21b6;
  color: white;
  padding: 0.6rem 1.2rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  width: 100%;
}

button:hover {
  opacity: 0.9;
}

.error-message {
  color: red;
  font-size: 12px;
  margin-top: 4px;
  text-align: right;
}
</style>