import {
  collection,
  addDoc,
  onSnapshot,
  query,
  orderBy,
  serverTimestamp
} from "firebase/firestore";
import { db } from "./firebase";

export function listenForRooms(callback) {
  const q = query(collection(db, "rooms"), orderBy("createdAt", "asc"));
  return onSnapshot(q, (snap) => {
    callback(snap.docs.map((d) => ({ id: d.id, ...d.data() })));
  });
}

export async function createRoom({ name, description, createdBy }) {
  return addDoc(collection(db, "rooms"), {
    name,
    description: description || "",
    createdBy,
    createdAt: serverTimestamp()
  });
}
