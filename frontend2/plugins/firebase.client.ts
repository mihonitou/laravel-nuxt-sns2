import { initializeApp } from "firebase/app";
import { getAuth } from "firebase/auth";

export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig();

  // 🔹 env 読み込み確認用
  console.log("Firebase API Key:", config.public.firebaseApiKey);

  const firebaseConfig: any = {
    apiKey: config.public.firebaseApiKey,
    authDomain: config.public.firebaseAuthDomain,
    projectId: config.public.firebaseProjectId,
    storageBucket: config.public.firebaseStorageBucket,
    messagingSenderId: config.public.firebaseMessagingSenderId,
    appId: config.public.firebaseAppId,
  };

  const app = initializeApp(firebaseConfig);
  const auth = getAuth(app);

  // 🔹 auth の確認用
  console.log("Auth Object:", auth);

  return {
    provide: {
      auth,
    },
  };
});
