<script setup>
import { ref, onMounted } from 'vue'
import { getAuth } from "firebase/auth"
import SideNav from '@/components/SideNav.vue'
import Message from '@/components/Message.vue'
import { useRouter } from 'vue-router'

const config = useRuntimeConfig()

// ✅ 追加（これだけ）
const { user } = useAuth()

const content = ref('')
const posts = ref([])

const fetchPosts = async () => {
  try {
    const res = await $fetch(`${config.public.apiBaseUrl}/api/posts`)
    posts.value = res
  } catch (e) {
    console.error("一覧取得エラー", e)
  }
}

onMounted(() => {
  fetchPosts()
})

// 👇 投稿
/*
const testPost = async () => {
  console.log("testPost start")

  // ✅ 追加（ログイン制御）
  if (!user.value) return alert('ログインが必要です')

  const auth = getAuth()

  const result = await signInAnonymously(auth)
  const firebaseUser = result.user  // ← 変更

  if (!firebaseUser) return

  const idToken = await firebaseUser.getIdToken()

  await $fetch(`${config.public.apiBaseUrl}/api/firebase-login`, {
    method: "POST",
    body: {
      idToken: idToken
    }
  })

  const res = await $fetch(`${config.public.apiBaseUrl}/api/posts`, {
    method: "POST",
    headers: {
      Authorization: `Bearer ${idToken}`
    },
    body: {
      content: content.value
    }
  })

  console.log("post result:", res)

  await fetchPosts()
  content.value = ''
}
*/

const router = useRouter()

const goToDetail = (id) => {
  router.push(`/posts/${id}`)
}

const onDelete = async (postId) => {
  try {
    console.log('削除対象:', postId)

    const auth = getAuth()
    const firebaseUser = auth.currentUser

    if (!firebaseUser) {
      alert('ログインが必要です')
      return
    }

    const idToken = await firebaseUser.getIdToken()

    await $fetch(
      `${config.public.apiBaseUrl}/api/posts/${postId}`,
      {
        method: 'DELETE',
        headers: {
          Authorization: `Bearer ${idToken}`
        }
      }
    )

    await fetchPosts()

  } catch (error) {
    console.error('削除失敗', error)

  }
}

</script>

<template>
  <div class="home-container">
    <SideNav @posted="fetchPosts" />

    <div class="main-area">
      <h1 class="page-title">ホーム</h1>
      <div class="title-line"></div>

      <div class="messages-wrapper">
        <Message
          v-for="post in posts"
          :key="post.id"
          :id="post.id"
          :author="post.user?.name ?? 'anonymous'"
          :content="post.content"
          :likeCount="post.likes_count"
          :showDetailIcon="true"
          :showDelete="post.user?.id === user?.id"
          @click="goToDetail(post.id)"
          @delete="onDelete"
          />
      </div>

    </div>
  </div>
</template>

<style scoped>
.home-container {
  display: flex;
  background-color: #111827; /* ← これ追加 */
  min-height: 100vh;
}

.main-area {
  flex: 1;
  padding: 30px;
  color: white; /* ← 文字見やすくする */
}

.page-title {
  font-size: 24px;
  margin-bottom: 6px;
}

.title-line {
  width: 100%;
  height: 2px;
  background: rgba(255, 255, 255, 0.3);
  margin-bottom: 20px;
}

.messages-wrapper {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

</style>
