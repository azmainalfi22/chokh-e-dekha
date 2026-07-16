import { z } from "zod";
import { CATEGORIES, CITIES } from "@/lib/constants";

export const loginSchema = z.object({
  email: z.email("Enter a valid email address"),
  password: z.string().min(1, "Password is required"),
});
export type LoginInput = z.infer<typeof loginSchema>;

export const signupSchema = z.object({
  displayName: z
    .string()
    .trim()
    .min(2, "Display name must be at least 2 characters")
    .max(50, "Display name must be under 50 characters"),
  email: z.email("Enter a valid email address"),
  password: z.string().min(8, "Password must be at least 8 characters"),
});
export type SignupInput = z.infer<typeof signupSchema>;

export const profileSchema = z.object({
  displayName: z
    .string()
    .trim()
    .min(2, "Display name must be at least 2 characters")
    .max(50, "Display name must be under 50 characters"),
  phone: z
    .string()
    .trim()
    .regex(/^(\+?880|0)1[3-9]\d{8}$/, "Enter a valid Bangladeshi mobile number")
    .or(z.literal(""))
    .optional(),
  city: z.string().trim().max(80).optional(),
});
export type ProfileInput = z.infer<typeof profileSchema>;

export const reportSchema = z.object({
  title: z
    .string()
    .trim()
    .min(8, "Title must be at least 8 characters")
    .max(120, "Title must be under 120 characters"),
  description: z
    .string()
    .trim()
    .min(20, "Describe the issue in at least 20 characters")
    .max(5000, "Description must be under 5000 characters"),
  category: z.enum(CATEGORIES, { error: "Pick a category" }),
  cityCorporation: z.enum(CITIES, { error: "Pick a city corporation" }),
  locationText: z.string().trim().max(240).optional(),
  latitude: z.number().min(-90).max(90).nullable(),
  longitude: z.number().min(-180).max(180).nullable(),
});
export type ReportInput = z.infer<typeof reportSchema>;

export const commentSchema = z.object({
  body: z
    .string()
    .trim()
    .min(1, "Comment cannot be empty")
    .max(2000, "Comment must be under 2000 characters"),
  parentId: z.number().int().positive().nullable().optional(),
});
export type CommentInput = z.infer<typeof commentSchema>;
