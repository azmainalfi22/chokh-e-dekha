import { ImageResponse } from "next/og";

// Brand favicon: the eye-with-map-pin mark on the national green tile.
export const size = { width: 32, height: 32 };
export const contentType = "image/png";

export default function Icon() {
  return new ImageResponse(
    (
      <div
        style={{
          width: "100%",
          height: "100%",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          background: "linear-gradient(160deg, #fdf7e9, #f4e6c4)",
          borderRadius: 7,
        }}
      >
        <svg width="26" height="26" viewBox="0 0 48 48">
          <circle cx="24" cy="20" r="16" fill="none" stroke="#e11d34" strokeWidth="3.6" />
          <path
            d="M24 5 C16.3 5 10 11 10 18.6 C10 28.5 24 43 24 43 C24 43 38 28.5 38 18.6 C38 11 31.7 5 24 5 Z"
            fill="none"
            stroke="#c8922f"
            strokeWidth="3.6"
            strokeLinejoin="round"
          />
          <path
            d="M24 7.4 C17.6 7.4 12.4 12.4 12.4 18.7 C12.4 27 24 40 24 40 C24 40 35.6 27 35.6 18.7 C35.6 12.4 30.4 7.4 24 7.4 Z"
            fill="#0c5942"
          />
          <path d="M24 12 L26 18 32 20 26 22 24 28 22 22 16 20 22 18 Z" fill="#e7c877" />
        </svg>
      </div>
    ),
    { ...size }
  );
}
