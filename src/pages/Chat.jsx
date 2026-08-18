import React, { useEffect, useRef, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { useAuth } from "../context/AuthContext.jsx";
import { listenForMessages, sendMessage } from "../services/messageService";
import RankBadge from "../components/RankBadge.jsx";
import "./Chat.css";

export default function Chat() {
  const { roomId } = useParams();
  const navigate = useNavigate();
  const { user, profile, rank } = useAuth();
  const [messages, setMessages] = useState([]);
  const [draft, setDraft] = useState("");
  const bottomRef = useRef(null);

  useEffect(() => {
    const unsub = listenForMessages(roomId, setMessages);
    return unsub;
  }, [roomId]);

  useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: "smooth" });
  }, [messages.length]);

  async function handleSend(e) {
    e.preventDefault();
    if (!draft.trim()) return;
    await sendMessage(roomId, {
      text: draft,
      senderId: user.uid,
      senderName: profile?.username || "مجهول",
      senderRank: rank
    });
    setDraft("");
  }

  return (
    <div className="chat-page">
      <header className="chat-header">
        <button className="btn-ghost" onClick={() => navigate("/rooms")} type="button">
          ← الغرف
        </button>
      </header>

      <div className="chat-messages scrollbar-thin">
        <div className="myth-divider">
          <span className="star">✶</span>
          <span>بداية المحادثة</span>
          <span className="star">✶</span>
        </div>

        {messages.map((m) => (
          <MessageBubble key={m.id} message={m} isOwn={m.senderId === user?.uid} />
        ))}
        <div ref={bottomRef} />
      </div>

      <form className="chat-composer" onSubmit={handleSend}>
        <input
          value={draft}
          onChange={(e) => setDraft(e.target.value)}
          placeholder="اكتب رسالتك…"
          maxLength={500}
        />
        <button type="submit" className="btn-primary">
          إرسال
        </button>
      </form>
    </div>
  );
}

function MessageBubble({ message, isOwn }) {
  // كل نص قادم من المستخدمين يُعرض هنا كنص عادي (React يهرب المحتوى تلقائيًا)
  // ولا نستخدم dangerouslySetInnerHTML إطلاقًا - هذا يمنع ثغرات XSS التي كانت
  // موجودة في النسخة القديمة عبر innerHTML.
  return (
    <div className={`message-row ${isOwn ? "own" : ""}`}>
      <RankBadge rank={message.senderRank} size={24} />
      <div className="message-bubble">
        <div className="message-meta">
          <span className="message-sender">{message.senderName}</span>
        </div>
        <p className="message-text">{message.text}</p>
      </div>
    </div>
  );
}
