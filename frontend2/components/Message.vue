
<template>
  <div class="message">
    <!-- 投稿ヘッダー -->
    <div class="message-header">
      <div class="left">
        <span class="author">{{ author }}</span>
      </div>

      <div class="delete-area">
        <button
        v-if="showDelete"
        class="icon-button"
        @click.stop="onDelete"
        >
        <img
        src="/images/cross-icon.png"
        class="icon-img"
        alt="削除"
        />
        </button>
      </div>
    </div>

    <!-- 投稿本文 -->
    <div class="message-content">
      {{ content }}
    </div>

    <!-- 投稿フッター -->
    <div class="message-actions">
      <!-- ハートボタン -->
      <button class="heart-button" :class="{ liked: liked }" @click.stop="onLike">
        <img
          src="/images/heart-icon.png"
          class="heart-icon"
          alt="like"
        />
      </button>
      <!-- いいね数 -->
      <span class="likes-count">{{ count }}</span>
      <!-- 詳細アイコン -->
      <button
        v-if="showDetailIcon"
        class="icon-button detail-icon"
        @click.stop="goDetail"
        aria-label="投稿の詳細を見る"
      >
      <img
        src="/images/detail-icon.png"
        class="icon-img"
        alt=""
      />
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue' // ★変更①（watch追加）
import { getAuth } from 'firebase/auth'
import { useAuth } from '@/composables/useAuth'
import { useRouter } from 'vue-router'

const { id, author, content, likeCount, showDetailIcon, showDelete } = defineProps({
  id: {
    type: String,
    required: true,
  },
  author: {
    type: String,
    required: true,
  },
  content: {
    type: String,
    required: true,
  },
  likeCount: {
    type: Number,
    default: 0
  },
  showDetailIcon: {
  type: Boolean,
  default: false
  },
  showDelete: {
  type: Boolean,
  default: false
  }
})

const emit = defineEmits<{
  (e: 'delete', postId: string): void
}>()

const count = ref(likeCount)

// ★追加②（これだけ）
watch(() => likeCount, (newVal) => {
  count.value = newVal
})

const liked = ref(likeCount > 0)

const { user } = useAuth()

const onLike = async () => {

  if (!user.value) return alert('ログインが必要です')

  const auth = getAuth()
  const firebaseUser = auth.currentUser
  if (!firebaseUser) return

  const idToken = await firebaseUser.getIdToken()

  try {
    if (!liked.value) {
      await fetch('http://localhost/api/likes', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${idToken}`
        },
        body: JSON.stringify({
          post_id: id,

        })
      })

      liked.value = true
      count.value++
    } else {
      await fetch('http://localhost/api/likes', {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${idToken}`
        },
        body: JSON.stringify({
          post_id: id,

        })
      })

      liked.value = false
      count.value--
    }
  } catch (e) {
    alert('失敗した')
  }
}

const router = useRouter()

const goDetail = () => {
  router.push(`/posts/${id}`)
}

const onDelete = () => {
  emit('delete', id)
}
</script>

<style scoped>
.message {
  position: relative;
  border: 2px solid rgba(255, 255, 255, 0.3);
  padding: 12px;
  background-color: #111827;
  color: white;
}

.message-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
  font-size: 14px;
}

.left {
  display: flex;
  align-items: center;
}

.author {
  font-weight: bold;
}

.delete-area {
  width: 32px;
  display: flex;
  justify-content: flex-end;
}

.message-content {
  font-size: 15px;
  margin-bottom: 8px;
}

.message-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.icon-button {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0;
}

.icon-img {
  width: 22px;
  height: 22px;
}

.heart-button {
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
}

.heart-icon {
  width: 24px;
  height: 24px;
  transition: filter 0.2s ease;
}

.heart-button.liked .heart-icon {
  filter: brightness(0) saturate(100%) invert(23%) sepia(96%)
    saturate(7492%) hue-rotate(358deg) brightness(95%) contrast(110%);
}

.detail-icon {
  position: absolute;
  bottom: 8px;
  right: 8px;
}

.likes-count {
  font-size: 14px;
}
</style>

