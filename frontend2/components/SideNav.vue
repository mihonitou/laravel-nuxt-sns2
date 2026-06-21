<template>
  <nav class="side-nav">
    <!-- ロゴ -->
    <div class="logo">
      <NuxtLink to="/">
        <img src="/images/logo.png" alt="SHARE ロゴ" class="logo-img" />
      </NuxtLink>
    </div>

    <!-- ホーム -->
    <NuxtLink to="/" class="nav-item">
      <img src="/images/home-icon.png" class="icon-img" />
      ホーム
    </NuxtLink>

    <!-- ログインと未ログイン切り替え -->
    <button
      v-if="user"
      class="nav-item"
      @click="logout"
    >
    <img src="/images/logout-icon.png" class="icon-img" />
      ログアウト
    </button>

    <button
      v-else
      class="nav-item"
      @click="router.push('/login')"
    >
    <img src="/images/login-icon.png" class="icon-img" />
      ログイン
    </button>

    <!-- 🔥 デバッグ表示（ここに追加） -->

    <!-- 投稿フォーム -->
    <div class="share-form">
      <textarea
      v-model="content"
      class="share-textarea"
      placeholder="いまどうしてる？"
      ></textarea>

      <p class="error-message">
        {{ errorMessage }}
      </p>

      <div class="share-button-wrapper">
        <button class="share-button" @click="sharePost">
        シェアする
        </button>
      </div>
    </div>
  </nav>
</template>

<script setup lang="ts">

import { useForm, useField } from 'vee-validate'
import * as yup from 'yup'
import { getAuth, signOut } from 'firebase/auth'
import { watch } from 'vue'


const config = useRuntimeConfig()
const router = useRouter()

// アプリ全体のログイン状態（useAuth） 🔥 ここで user 取得
const { user, setUser, clearUser } = useAuth()

// 🔥 その直下に書く（ここが正解位置）
watch(user, (val) => {
  console.log('👤 user変化:', val)
})

const schema = yup.object({
  content: yup
    .string()
    .required('投稿内容を入力してください')
    .max(120, '120文字以内で入力してください')
})

const { handleSubmit } = useForm({
  validationSchema: schema
})

const { value: content, errorMessage } = useField<string>('content')

const emit = defineEmits(['posted'])

const props = defineProps({
  showPost: {
    type: Boolean,
    default: true
  }
})

const sharePost = handleSubmit(async () => {
  console.log('🔥 投稿時user', user.value)

  if (!user.value) return alert('ログインが必要です')

  const idToken = localStorage.getItem('idToken')

  if (!idToken) {
    return alert('ログインが必要です')
  }

  await $fetch(`${config.public.apiBaseUrl}/api/posts`, {
    method: "POST",
    headers: {
      Authorization: `Bearer ${idToken}`
    },
    body: {
      content: content.value
    }
  })

  content.value = ''
  emit('posted')
})

const logout = async () => {
  const auth = getAuth()

  try {
    // Firebaseログアウト
    await signOut(auth)

    // localStorage削除（超重要）
    localStorage.removeItem('idToken')

    // Nuxt状態クリア
    clearUser()

    console.log('ログアウト成功')

    router.replace('/login')

  } catch (error) {
    console.error('ログアウト失敗:', error)
  }
}
</script>

<style scoped>
/* サイドバー全体 */
.side-nav {
  width: 260px;
  background: #111827;
  color: white;
  height: 100vh;
  padding: 20px;
  display: flex;
  flex-direction: column;
}

/* ロゴ */
.logo-img {
  width: 140px;
  margin-bottom: 20px;
}

/* ナビ項目 */
.nav-item {
  display: flex;
  align-items: center;
  padding: 10px 0;
  color: white;
  cursor: pointer;
  text-decoration: none; /* ← これを追加 */
  background: transparent;   /* ← 追加 */
  border: none;               /* ← 追加 */
}

.nav-item:hover {
  opacity: 0.8;
}

/* アイコン */
.icon-img {
  width: 20px;
  margin-right: 10px;
}

/* 投稿フォーム全体 */
.share-form {
  margin-top: 20px;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: flex-end; /* ← 右端基準 */
}

/* textarea */
.share-textarea {
  width: 90%;          /* ← 少し小さく */
  height: 70px;
  padding: 10px;
  border-radius: 6px;
}

/* ボタンラッパー */
.share-button-wrapper {
  width: 90%;          /* ← textarea と同じ幅 */
  display: flex;
  justify-content: flex-end;
}

/* ボタン */
.share-button {
  width: 50%;
  padding: 10px;
  margin-top: 8px;
  border-radius: 6px;
  background: #5b21b6;
  color: white;
}


/* エラー表示 */
.error-message {
  color: red;
  font-size: 12px;
  margin-top: 4px;
}
</style>
