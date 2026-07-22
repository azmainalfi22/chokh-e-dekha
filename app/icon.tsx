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
          background: "linear-gradient(160deg, #0a7a5a, #045c43)",
          borderRadius: 7,
        }}
      >
        <svg
          width="22"
          height="22"
          viewBox="0 0 24 24"
          fill="none"
          stroke="#ffffff"
          strokeWidth={1.9}
          strokeLinecap="round"
          strokeLinejoin="round"
        >
          <path d="M1.8 12S5.5 5.8 12 5.8 22.2 12 22.2 12 18.5 18.2 12 18.2 1.8 12 1.8 12Z" />
          <path d="M12 8.2a2.7 2.7 0 0 0-2.7 2.7c0 1.9 2.7 4.3 2.7 4.3s2.7-2.4 2.7-4.3A2.7 2.7 0 0 0 12 8.2Z" />
          <circle cx="12" cy="10.9" r="0.9" fill="#ffffff" stroke="none" />
        </svg>
      </div>
    ),
    { ...size }
  );
}
