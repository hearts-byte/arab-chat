// src/constants/ranks.js
//
// هذا الترتيب هو "مصدر الحقيقة" لعرض الرتب في الواجهة فقط.
// التحقق الفعلي من الصلاحيات (من يقدر يحذف رسالة، يمسح غرفة، إلخ)
// يجب أن يحدث في firestore.rules على السيرفر - لا يكفي إخفاء زر في الواجهة.

export const RANKS = {
  OWNER: "المالك",
  OWNER_ADMIN: "اونر اداري",
  OWNER_2: "اونر",
  SUPER_ADMIN_2: "سوبر اداري",
  SUPERVISOR: "مشرف",
  SUPER_ADMIN: "سوبر ادمن",
  ADMIN: "ادمن",
  PREMIUM: "بريميوم",
  PLATINUM: "بلاتينيوم",
  ROYAL: "ملكي",
  GOLD: "ذهبي",
  BRONZE: "برونزي",
  MEMBER: "عضو",
  GUEST: "زائر"
};

export const RANK_ORDER = [
  RANKS.OWNER,
  RANKS.OWNER_ADMIN,
  RANKS.OWNER_2,
  RANKS.SUPER_ADMIN_2,
  RANKS.SUPERVISOR,
  RANKS.SUPER_ADMIN,
  RANKS.ADMIN,
  RANKS.PREMIUM,
  RANKS.PLATINUM,
  RANKS.ROYAL,
  RANKS.GOLD,
  RANKS.BRONZE,
  RANKS.MEMBER,
  RANKS.GUEST
];

// الرتب التي تُعتبر "إدارة" (staff) - تُستخدم فقط لإظهار/إخفاء عناصر الواجهة.
// النسخة المُلزمة فعليًا من هذه القائمة موجودة في firestore.rules
export const STAFF_RANKS = new Set([
  RANKS.OWNER,
  RANKS.OWNER_ADMIN,
  RANKS.OWNER_2,
  RANKS.SUPER_ADMIN_2,
  RANKS.SUPERVISOR,
  RANKS.SUPER_ADMIN,
  RANKS.ADMIN
]);

export function isStaff(rank) {
  return STAFF_RANKS.has(rank);
}

export function rankBadge(rank) {
  const map = {
    [RANKS.OWNER]: "/ranks/owner.png",
    [RANKS.OWNER_ADMIN]: "/ranks/owner_admin.png",
    [RANKS.OWNER_2]: "/ranks/owner2.png",
    [RANKS.SUPER_ADMIN_2]: "/ranks/super_admin.png",
    [RANKS.SUPERVISOR]: "/ranks/supervisor.png",
    [RANKS.SUPER_ADMIN]: "/ranks/super_admn.png",
    [RANKS.ADMIN]: "/ranks/admin.png",
    [RANKS.PREMIUM]: "/ranks/premium.png",
    [RANKS.PLATINUM]: "/ranks/platinum.png",
    [RANKS.ROYAL]: "/ranks/royal.png",
    [RANKS.GOLD]: "/ranks/gold.png",
    [RANKS.BRONZE]: "/ranks/bronze.png",
    [RANKS.MEMBER]: "/ranks/member.png",
    [RANKS.GUEST]: "/ranks/guest.png"
  };
  return map[rank] || map[RANKS.GUEST];
}
