import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext.jsx";
import { listenForRooms, createRoom } from "../services/roomService";
import RankBadge from "../components/RankBadge.jsx";
import "./Rooms.css";

export default function Rooms() {
  const { profile, rank, signOut } = useAuth();
  const navigate = useNavigate();
  const [rooms, setRooms] = useState([]);
  const [showCreate, setShowCreate] = useState(false);
  const [newRoomName, setNewRoomName] = useState("");

  useEffect(() => {
    const unsub = listenForRooms(setRooms);
    return unsub;
  }, []);

  async function handleCreateRoom(e) {
    e.preventDefault();
    if (!newRoomName.trim()) return;
    await createRoom({ name: newRoomName.trim(), createdBy: profile?.id });
    setNewRoomName("");
    setShowCreate(false);
  }

  return (
    <div className="rooms-page">
      <header className="rooms-header">
        <div className="rooms-user">
          <RankBadge rank={rank} size={28} />
          <div>
            <div className="rooms-username">{profile?.username || "…"}</div>
            <div className="rooms-userrank">{rank}</div>
          </div>
        </div>
        <button className="btn-ghost" onClick={signOut} type="button">
          خروج
        </button>
      </header>

      <div className="myth-divider">
        <span className="star">✶</span>
        <span>الغرف</span>
        <span className="star">✶</span>
      </div>

      <div className="rooms-grid">
        {rooms.map((room) => (
          <button
            key={room.id}
            className="room-card"
            onClick={() => navigate(`/chat/${room.id}`)}
            type="button"
          >
            <h3>{room.name}</h3>
            {room.description && <p>{room.description}</p>}
          </button>
        ))}

        <button className="room-card room-card--add" onClick={() => setShowCreate(true)} type="button">
          + غرفة جديدة
        </button>
      </div>

      {showCreate && (
        <div className="modal-backdrop" onClick={() => setShowCreate(false)}>
          <form className="modal-card" onClick={(e) => e.stopPropagation()} onSubmit={handleCreateRoom}>
            <h3>إنشاء غرفة جديدة</h3>
            <input
              autoFocus
              placeholder="اسم الغرفة"
              value={newRoomName}
              onChange={(e) => setNewRoomName(e.target.value)}
              maxLength={40}
            />
            <div className="modal-actions">
              <button type="button" className="btn-ghost" onClick={() => setShowCreate(false)}>
                إلغاء
              </button>
              <button type="submit" className="btn-primary">
                إنشاء
              </button>
            </div>
          </form>
        </div>
      )}
    </div>
  );
}
