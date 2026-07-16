"use client";

import {
  Area,
  AreaChart,
  Bar,
  BarChart,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";

const BRAND = "var(--brand-orange)";
const GRID = "color-mix(in oklch, var(--muted-foreground) 18%, transparent)";
const INK = "var(--muted-foreground)";

const tooltipStyle: React.CSSProperties = {
  background: "var(--popover)",
  border: "1px solid var(--border)",
  borderRadius: 10,
  color: "var(--popover-foreground)",
  fontSize: 12,
  boxShadow: "0 4px 16px rgb(0 0 0 / 0.12)",
};

/** Weekly submissions — single-series area, crosshair tooltip. */
export function TrendChart({
  data,
}: {
  data: { week: string; count: number }[];
}) {
  return (
    <ResponsiveContainer width="100%" height={240}>
      <AreaChart data={data} margin={{ top: 8, right: 8, left: -18, bottom: 0 }}>
        <defs>
          <linearGradient id="trendFill" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stopColor={BRAND} stopOpacity={0.28} />
            <stop offset="100%" stopColor={BRAND} stopOpacity={0.02} />
          </linearGradient>
        </defs>
        <CartesianGrid stroke={GRID} vertical={false} />
        <XAxis
          dataKey="week"
          tick={{ fill: INK, fontSize: 11 }}
          tickLine={false}
          axisLine={false}
        />
        <YAxis
          allowDecimals={false}
          tick={{ fill: INK, fontSize: 11 }}
          tickLine={false}
          axisLine={false}
          width={40}
        />
        <Tooltip
          contentStyle={tooltipStyle}
          cursor={{ stroke: GRID }}
          formatter={(v) => [v as number, "Reports"]}
        />
        <Area
          type="monotone"
          dataKey="count"
          stroke={BRAND}
          strokeWidth={2}
          fill="url(#trendFill)"
          dot={false}
          activeDot={{ r: 4 }}
        />
      </AreaChart>
    </ResponsiveContainer>
  );
}

/** Magnitude comparison — horizontal single-hue bars with value labels. */
export function BreakdownBars({
  data,
  height,
}: {
  data: { name: string; count: number }[];
  height?: number;
}) {
  return (
    <ResponsiveContainer width="100%" height={height ?? Math.max(160, data.length * 36)}>
      <BarChart
        data={data}
        layout="vertical"
        margin={{ top: 0, right: 28, left: 8, bottom: 0 }}
        barCategoryGap={6}
      >
        <CartesianGrid stroke={GRID} horizontal={false} />
        <XAxis type="number" hide allowDecimals={false} />
        <YAxis
          type="category"
          dataKey="name"
          width={150}
          tick={{ fill: INK, fontSize: 11 }}
          tickLine={false}
          axisLine={false}
        />
        <Tooltip
          contentStyle={tooltipStyle}
          cursor={{ fill: GRID }}
          formatter={(v) => [v as number, "Reports"]}
        />
        <Bar
          dataKey="count"
          fill={BRAND}
          radius={[0, 4, 4, 0]}
          maxBarSize={18}
          label={{ position: "right", fill: INK, fontSize: 11 }}
        />
      </BarChart>
    </ResponsiveContainer>
  );
}
