import React, { createContext, useContext, useEffect, useState } from "react";
import { onAuthStateChanged, signOut as firebaseSignOut } from "firebase/auth";
import { doc, onSnapshot } from "firebase/firestore";
import { auth, db } from "../services/firebase";
import { RANKS } from "../constants/ranks";

// ملاحظة أمنية مهمة:
// لا نخزّن الرتبة في localStorage ونثق بها. بدلًا من ذلك نستمع (onSnapshot)
// لمستند المستخدم في Firestore مباشرة، فأي تغيير في الرتبة من لوحة الإدارة
// ينعكس فورًا، ولا يقدر المستخدم "يزوّر" رتبته من أدوات المطوّر لأن الواجهة
// تعرض فقط ما هو مخزن فعليًا في قاعدة البيانات. الفرض الحقيقي للصلاحيات
// (من يقدر يحذف/يمسح) يتم في firestore.rules بغض النظر عمّا تعرضه الواجهة.

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [firebaseUser, setFirebaseUser] = useState(null);
  const [profile, setProfile] = useState(null); // مستند المستخدم من Firestore
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const unsubAuth = onAuthStateChanged(auth, (user) => {
      setFirebaseUser(user);
      if (!user) {
        setProfile(null);
        setLoading(false);
      }
    });
    return unsubAuth;
  }, []);

  useEffect(() => {
    if (!firebaseUser) return;
    const ref = doc(db, "users", firebaseUser.uid);
    const unsubProfile = onSnapshot(ref, (snap) => {
      setProfile(snap.exists() ? { id: snap.id, ...snap.data() } : null);
      setLoading(false);
    });
    return unsubProfile;
  }, [firebaseUser]);

  const value = {
    user: firebaseUser,
    profile,
    rank: profile?.rank || RANKS.GUEST,
    loading,
    isAuthenticated: !!firebaseUser,
    signOut: () => firebaseSignOut(auth)
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error("useAuth يجب أن يُستخدم داخل AuthProvider");
  return ctx;
}
