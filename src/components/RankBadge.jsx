import React from "react";
import { rankBadge } from "../constants/ranks";

export default function RankBadge({ rank, size = 20 }) {
  return (
    <img
      src={rankBadge(rank)}
      alt={rank}
      title={rank}
      width={size}
      height={size}
      style={{ borderRadius: "50%", objectFit: "cover" }}
    />
  );
}
