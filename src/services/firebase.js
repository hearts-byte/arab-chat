// src/services/firebase.js
//
// إعدادات Firebase تُقرأ من متغيرات البيئة (.env) وليست مكتوبة هنا مباشرة.
// مفتاح الـ apiKey الخاص بواجهة الويب ليس سرًّا حساسًا بطبيعته (Firebase مصمم
// على هذا الأساس)، لكن الحماية الحقيقية تأتي من قواعد أمان Firestore
// (انظر firestore.rules) وليس من إخفاء هذا المفتاح.
//
// أما أي مفتاح "Admin SDK" أو مفاتيح خدمات إدارية (Algolia Admin، إلخ) فلا
// يجب أن تظهر في كود الواجهة الأمامية إطلاقًا - تلك تبقى فقط على السيرفر.

import { initializeApp } from "firebase/app";
import { getAuth } from "firebase/auth";
import { getFirestore } from "firebase/firestore";

const firebaseConfig = {
  apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
  authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
  projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
  storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
  messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
  appId: import.meta.env.VITE_FIREBASE_APP_ID
};

export const app = initializeApp(firebaseConfig);
export const auth = getAuth(app);
export const db = getFirestore(app);
