import {
  Biohazard,
  Building2,
  CircleHelp,
  Construction,
  Droplets,
  Flame,
  Lightbulb,
  ShieldAlert,
  TrafficCone,
  Trash2,
  Trees,
  Waves,
  Zap,
  type LucideIcon,
} from "lucide-react";
import { cn } from "@/lib/utils";

/**
 * The icon that stands for each report category.
 *
 * Most reports arrive without a photograph, so the card's media area is empty
 * far more often than not. A blank gradient with a generic pin reads as a
 * missing image; a category tile reads as a design decision, and it also tells
 * the reader something — you can scan a feed by icon.
 */
export const CATEGORY_ICONS: Record<string, LucideIcon> = {
  Road: Construction,
  "Garbage / Waste": Trash2,
  Streetlight: Lightbulb,
  "Drainage / Waterlogging": Waves,
  "Water Supply": Droplets,
  Sewerage: Biohazard,
  "Illegal Construction": Building2,
  Traffic: TrafficCone,
  "Public Safety": ShieldAlert,
  Electricity: Zap,
  Gas: Flame,
  Parks: Trees,
  Other: CircleHelp,
};

export function categoryIcon(category: string): LucideIcon {
  return CATEGORY_ICONS[category] ?? CircleHelp;
}

/**
 * Stand-in for a report's photograph.
 *
 * Deliberately quiet: a soft diagonal wash and a single large glyph, so a feed
 * of reports without photos still looks composed rather than unfinished.
 */
export function CategoryTile({
  category,
  className,
}: {
  category: string;
  className?: string;
}) {
  const Icon = categoryIcon(category);

  return (
    <span
      className={cn(
        "bg-brand-gradient relative flex size-full items-center justify-center overflow-hidden",
        className
      )}
      aria-hidden
    >
      {/* A faint repeat of the glyph, offset, so the tile has some depth. */}
      <Icon className="absolute -right-6 -bottom-8 size-40 text-white/10" />
      <Icon className="relative size-11 text-white/90" strokeWidth={1.5} />
    </span>
  );
}
