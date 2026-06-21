<script setup lang="ts">
import { onMounted } from 'vue'
import { onAuthStateChanged } from "firebase/auth"

import { useAuth } from '~/composables/useAuth'

const { setUser } = useAuth()

const { $auth } = useNuxtApp()

// 🔥 自動ログイン
onMounted(() => {

  console.log("🔥 onMounted開始")

  onAuthStateChanged($auth, async (firebaseUser) => {

    console.log("🔥 onAuthStateChanged発火")
    console.log("🔥 firebaseUser", firebaseUser)

    try {

      // Firebase未ログインなら終了
      if (!firebaseUser) {
        console.log("未ログイン")
        return
      }

      // Firebaseから最新token取得
      const idToken = await firebaseUser.getIdToken()

      // 念のため保存し直す
      localStorage.setItem("idToken", idToken)

      // Laravelへ
      const me: any = await $fetch("http://localhost/api/me", {
        method: "POST",
        headers: {
          Authorization: `Bearer ${idToken}`
        }
      })

      setUser(me.user)

      console.log("🔁 自動ログイン成功")

    } catch (e) {

      console.error("自動ログイン失敗", e)

    }

  })

})
</script>

<template>
  <div class="layout-wrapper">
    <slot />
  </div>
</template>

<style>
html, body {
  margin: 0;
  padding: 0;
  overflow-x: hidden;
}

.layout-wrapper {
  background: #111827;
  min-height: 100vh;
  width: 100%;
  overflow-x: hidden;
}
</style>