import {
  signInAnonymously,
  createUserWithEmailAndPassword,
  signInWithEmailAndPassword,
  updateProfile
} from "firebase/auth";
import { doc, setDoc, serverTimestamp } from "firebase/firestore";
import { auth, db } from "./firebase";
import { RANKS } from "../constants/ranks";

// كل حساب جديد يُنشأ برتبة ثابتة من هذه القائمة فقط (لا يمكن للعميل إرسال
// رتبة اختيارية). ترقية الرتبة لاحقًا تتم من لوحة إدارة منفصلة، ويتحقق منها
// firestore.rules عند الكتابة - لا تكفي هذه الدالة وحدها.

export async function registerGuest(displayName) {
  const cred = await signInAnonymously(auth);
  await setDoc(doc(db, "users", cred.user.uid), {
    username: displayName,
    rank: RANKS.GUEST,
    avatar: "/avatars/default-visitor.png",
    createdAt: serverTimestamp(),
    isAnonymous: true
  });
  return cred.user;
}

export async function registerMember(email, password, displayName) {
  const cred = await createUserWithEmailAndPassword(auth, email, password);
  await updateProfile(cred.user, { displayName });
  await setDoc(doc(db, "users", cred.user.uid), {
    username: displayName,
    rank: RANKS.MEMBER,
    avatar: "/avatars/default-user.png",
    createdAt: serverTimestamp(),
    isAnonymous: false
  });
  return cred.user;
}

export async function loginMember(email, password) {
  const cred = await signInWithEmailAndPassword(auth, email, password);
  return cred.user;
}
