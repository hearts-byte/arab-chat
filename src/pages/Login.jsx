import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import { registerGuest, registerMember, loginMember } from "../services/authService";
import "./Login.css";

const TABS = {
  GUEST: "guest",
  REGISTER: "register",
  LOGIN: "login"
};

export default function Login() {
  const navigate = useNavigate();
  const [tab, setTab] = useState(TABS.GUEST);
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [busy, setBusy] = useState(false);

  async function handleSubmit(e) {
    e.preventDefault();
    setError("");
    setBusy(true);
    try {
      if (tab === TABS.GUEST) {
        if (!name.trim()) throw new Error("اكتب اسمًا للدخول كزائر");
        await registerGuest(name.trim());
      } else if (tab === TABS.REGISTER) {
        if (!name.trim() || !email.trim() || password.length < 6) {
          throw new Error("تأكد من تعبئة الاسم والبريد، وكلمة مرور 6 أحرف فأكثر");
        }
        await registerMember(email.trim(), password, name.trim());
      } else {
        await loginMember(email.trim(), password);
      }
      navigate("/rooms");
    } catch (err) {
      setError(readableError(err));
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="login-page">
      <div className="login-card">
        <div className="myth-divider" style={{ marginTop: 0 }}>
          <span className="star">✶</span>
        </div>
        <h1 className="display login-title">ليالٍ</h1>
        <p className="login-subtitle">دردشة عربية بطابع الأساطير</p>

        <div className="login-tabs" role="tablist">
          <button
            role="tab"
            aria-selected={tab === TABS.GUEST}
            className={tab === TABS.GUEST ? "active" : ""}
            onClick={() => setTab(TABS.GUEST)}
            type="button"
          >
            دخول كزائر
          </button>
          <button
            role="tab"
            aria-selected={tab === TABS.REGISTER}
            className={tab === TABS.REGISTER ? "active" : ""}
            onClick={() => setTab(TABS.REGISTER)}
            type="button"
          >
            إنشاء حساب
          </button>
          <button
            role="tab"
            aria-selected={tab === TABS.LOGIN}
            className={tab === TABS.LOGIN ? "active" : ""}
            onClick={() => setTab(TABS.LOGIN)}
            type="button"
          >
            تسجيل الدخول
          </button>
        </div>

        <form onSubmit={handleSubmit} className="login-form">
          {(tab === TABS.GUEST || tab === TABS.REGISTER) && (
            <label className="field">
              <span>الاسم</span>
              <input value={name} onChange={(e) => setName(e.target.value)} maxLength={24} />
            </label>
          )}
          {(tab === TABS.REGISTER || tab === TABS.LOGIN) && (
            <>
              <label className="field">
                <span>البريد الإلكتروني</span>
                <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} />
              </label>
              <label className="field">
                <span>كلمة المرور</span>
                <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} />
              </label>
            </>
          )}

          {error && <p className="login-error">{error}</p>}

          <button type="submit" className="btn-primary" disabled={busy}>
            {busy ? "جارٍ الدخول…" : tab === TABS.LOGIN ? "دخول" : "متابعة"}
          </button>
        </form>
      </div>
    </div>
  );
}

function readableError(err) {
  const code = err?.code || "";
  if (code.includes("email-already-in-use")) return "هذا البريد مستخدم بالفعل";
  if (code.includes("invalid-credential") || code.includes("wrong-password")) return "بيانات الدخول غير صحيحة";
  if (code.includes("user-not-found")) return "لا يوجد حساب بهذا البريد";
  if (code.includes("weak-password")) return "كلمة المرور ضعيفة جدًا";
  return err.message || "حدث خطأ غير متوقع، حاول مرة أخرى";
}
