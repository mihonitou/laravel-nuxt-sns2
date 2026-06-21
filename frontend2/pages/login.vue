<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useForm, useField } from 'vee-validate'
import * as yup from 'yup'

import { getAuth, signInWithEmailAndPassword, onAuthStateChanged } from "firebase/auth"

import { useAuth } from '~/composables/useAuth'
import { useRouter } from 'vue-router'

const { $auth } = useNuxtApp()

const schema = yup.object({
  email: yup
    .string()
    .required('メールアドレスを入力してください'),

  password: yup
    .string()
    .required('パスワードを入力してください')
})

const { handleSubmit } = useForm({
  validationSchema: schema
})

const { value: email, errorMessage: emailError }
  = useField<string>('email')

const { value: password, errorMessage: passwordError }
  = useField<string>('password')

const { setUser, user } = useAuth()
const router = useRouter()

const login = handleSubmit(async () => {
  try {
    const auth = getAuth()

    // 🔥 メール/パスワードログイン
    const userCredential = await signInWithEmailAndPassword(
      auth,
      email.value,
      password.value
    )

    const firebaseUser = userCredential.user
    if (!firebaseUser) throw new Error("User not found")

    // 🔥 IDトークン取得
    const idToken = await firebaseUser.getIdToken(true)

    localStorage.setItem('idToken', idToken)

    // 🔥 Laravelログイン
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

    console.log('🔥 me結果', me)
    console.log('🔥 me.user', me.user)

    setUser(me.user)

    await router.push('/')

    alert("ログイン成功！")

  } catch (e: any) {
    console.error("❌ ログインエラー", e)
    alert("ログイン失敗（メール or パスワード確認）")
  }
})

</script>

<template>
  <div class="login-page">
    <!-- 共通ヘッダー -->
    <AuthHeader />

    <!-- ログインフォーム -->
    <div class="form-container">
      <h2>ログイン</h2>

      <!-- メール -->
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

      <button @click="login">
        ログイン
      </button>
    </div>
  </div>
</template>

<style scoped>
.login-page {
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