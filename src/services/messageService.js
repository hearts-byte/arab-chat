import {
  collection,
  addDoc,
  query,
  orderBy,
  limit,
  onSnapshot,
  serverTimestamp
} from "firebase/firestore";
import { db } from "./firebase";

const MESSAGE_PAGE_SIZE = 50;

export function listenForMessages(roomId, callback) {
  const q = query(
    collection(db, "rooms", roomId, "messages"),
    orderBy("createdAt", "asc"),
    limit(MESSAGE_PAGE_SIZE)
  );
  return onSnapshot(q, (snap) => {
    callback(snap.docs.map((d) => ({ id: d.id, ...d.data() })));
  });
}

// ملاحظة: senderId يجب أن يطابق uid المستخدم المصادق - يُفرض هذا في
// firestore.rules (request.auth.uid == resource.data.senderId عند الإرسال)
// حتى لا يقدر أحد ينتحل اسم أو هوية شخص آخر بتعديل الطلب من المتصفح.
export async function sendMessage(roomId, { text, senderId, senderName, senderRank }) {
  const trimmed = text.trim();
  if (!trimmed) return;
  return addDoc(collection(db, "rooms", roomId, "messages"), {
    text: trimmed,
    senderId,
    senderName,
    senderRank,
    createdAt: serverTimestamp()
  });
}
