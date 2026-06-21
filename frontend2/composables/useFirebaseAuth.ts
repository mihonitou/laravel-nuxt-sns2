import { getAuth, signInAnonymously } from "firebase/auth";

export async function loginAnonymously() {
  const auth = getAuth();
  try {
    const userCredential = await signInAnonymously(auth);
    const user = userCredential.user;
    const idToken = await user.getIdToken();
    return { user, idToken };
  } catch (err) {
    console.error("匿名ログイン失敗", err);
    throw err;
  }
}
