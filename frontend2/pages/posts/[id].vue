<script setup lang="ts">
import { useForm, useField } from 'vee-validate'
import * as yup from 'yup'

import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import SideNav from '@/components/SideNav.vue'
import Message from '@/components/Message.vue'

const route = useRoute()
const config = useRuntimeConfig()

const { user, setUser } = useAuth()

const post = ref<any>(null)
const comments = ref<any[]>([])
const newCommentAuthor = ref('')

const commentSchema = yup.object({
  content: yup
    .string()
    .required('コメントを入力してください')
    .max(120, '120文字以内で入力してください')
})

const { handleSubmit } = useForm({
  validationSchema: commentSchema
})

const { value: newComment, errorMessage: commentError } =
  useField<string>('content')

const postId = route.params.id

const fetchPost = async () => {
  try {
    const res = await $fetch(`${config.public.apiBaseUrl}/api/posts/${postId}`)
    console.log('post:', JSON.stringify(res, null, 2))
    post.value = res
  } catch (e) {
    console.error('詳細取得エラー', e)
  }
}

const addComment = handleSubmit(async () => {

  console.log('🔥 comment user', user.value)

  if (!user.value) return alert('ログインが必要です')

  const idToken = localStorage.getItem('idToken')

  if (!idToken) {
    return alert('ログインが必要です')
  }

  try {
    await fetch(`${config.public.apiBaseUrl}/api/posts/${postId}/comments`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${idToken}`
      },
      body: JSON.stringify({
        content: newComment.value
      })
    })

    newComment.value = ''

    const res = await fetch(`${config.public.apiBaseUrl}/api/posts/${postId}/comments`)
    const data = await res.json()
    comments.value = data

  } catch (e) {
    console.error('コメント投稿エラー', e)
  }
})

onMounted(async () => {
  console.log('① mounted開始')

  const idToken = localStorage.getItem('idToken')

  try {
    if (idToken) {
      const me: any = await $fetch(
        `${config.public.apiBaseUrl}/api/me`,
        {
          method: "POST",
          headers: {
            Authorization: `Bearer ${idToken}`
          }
        }
      )

      console.log('③ me成功', me)

      setUser(me.user)
    }
  } catch (e) {
    console.error('🔥 me取得失敗', e)
  }

  // me失敗しても続行
  await fetchPost()

  try {
    const res = await fetch(
      `${config.public.apiBaseUrl}/api/posts/${postId}/comments`
    )

    const data = await res.json()

    comments.value = data

  } catch (e) {
    console.error('コメント取得エラー', e)
  }
})
</script>

<template>
  <div class="page">
    <SideNav :showPost="false" />

    <div class="content">
      <h2 class="title">投稿詳細</h2>

      <div v-if="post">
        <Message
          :id="post.id"
          :author="post.user?.name ?? 'anonymous'"
          :content="post.content"
          :likeCount="post.likes_count"
        />
      </div>

      <div v-else>
        読み込み中...
      </div>

    <h3 class="section-title">コメント</h3>

      <div class="comment-area">
        <div v-for="comment in comments" :key="comment.id">
          <div>{{ comment.user?.name ?? 'anonymous' }}</div>
          <div>{{ comment.content }}</div>
        </div>
      </div>

      <div class="comment-form">
        <input
          type="text"
          v-model="newCommentAuthor"
          class="comment-input"
          placeholder="ユーザーネーム"
        />
        <textarea
        v-model="newComment"
        class="comment-input"
        placeholder="コメントを入力"
        ></textarea>

        <p class="error-message">
        {{ commentError }}
        </p>

        <button class="comment-button" @click="addComment">
          コメント追加
        </button>
      </div>

    </div>
  </div>
</template>

<style scoped>
.page {
  display: flex;
}

/* メイン */
.content {
  flex: 1;
  padding: 30px;
  color: white;

}

/* 一番上タイトル（左寄せ） */
.title {
  font-size: 24px;
  margin-bottom: 20px;
}

/* 投稿詳細タイトル直下の区切り線 */
.title::after {
  content: "";
  display: block;
  width: 100%;
  height: 2px;
  background: rgba(255, 255, 255, 0.3);
  margin: 10px 0 0;
}

/* セクション見出し */
.section-title {
  position: relative;
  font-size: 20px;
  margin: 30px 0;
  text-align: center;
  color: white;
}

/* 上下線 */
.section-title::before,
.section-title::after {
  content: '';
  position: absolute;
  left: 0;
  width: 100%;
  height: 2px;
  background: rgba(255, 255, 255, 0.3);
}
.section-title::before { top: -12px; }
.section-title::after { bottom: -12px; }

/* コメント表示 */
.comment-area {
  margin-bottom: 25px;
}

/* コメントフォーム */
.comment-form {
  margin-top: 10px;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 6px;
}

.comment-input {
  width: 100%;
  padding: 10px;
  border-radius: 10px;
  resize: none;
  background: white;
  color: #111;
  border: 1px solid #444;
}

.comment-button {
  margin-top: 5px;
  padding: 10px 20px;
  background: #5b21b6;
  border-radius: 8px;
  color: white;
}

.error-message {
  color: red;
  font-size: 12px;
  margin-top: 4px;
}
</style>


