<template>
  <header class="auth-header">
    <div class="inner">
      <!-- ロゴ -->
      <NuxtLink to="/" class="logo-link">
        <img src="/images/logo.png" alt="SHARE ロゴ" class="logo-img" />
      </NuxtLink>

      <!-- 右ボタン -->
      <nav class="nav-buttons">
        <NuxtLink to="/register" class="nav-btn">新規登録</NuxtLink>
        <NuxtLink to="/login" class="nav-btn">ログイン</NuxtLink>
      </nav>
    </div>
  </header>
</template>

<style scoped>
.auth-header {
  background: #111827;
  padding: 16px 16px; /* ← 24pxだと画面の狭い環境で右側がはみ出す */
  width: 100%;
}

/* 最大幅を中央に固定する */
.inner {
  max-width: 1200px;      /* ← 横に広がりすぎない（重要） */
  margin: 0 auto;         /* 中央寄せ */
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo-link {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.logo-img {
  height: 40px;
  width: auto;
}

.nav-buttons {
  display: flex;
  gap: 16px;
}

.nav-btn {
  color: #fff;
  text-decoration: none;
  font-weight: bold;
  border: 1px solid #fff;
  padding: 6px 12px;
  border-radius: 6px;
  transition: background 0.2s;
}

.nav-btn:hover {
  background: #fff;
  color: #000;
}
</style>
