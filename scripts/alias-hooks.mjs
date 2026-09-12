import path from "node:path";
import { fileURLToPath, pathToFileURL } from "node:url";

/**
 * Teaches Node's module loader the "@/" path alias.
 *
 * The codebase imports as `@/lib/constants`, which Next resolves through
 * tsconfig `paths`. Node's own resolver knows nothing about tsconfig, so a test
 * that reaches any module using the alias fails to load. Rather than rewrite the
 * source to relative imports for the benefit of the test runner, this maps the
 * alias the same way tsconfig does.
 *
 * Only "@/" is handled; everything else falls through untouched.
 */
const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");

export async function resolve(specifier, context, nextResolve) {
  if (!specifier.startsWith("@/")) {
    return nextResolve(specifier, context);
  }

  const target = path.join(projectRoot, specifier.slice(2));

  // Source files are TypeScript and the alias is written without an extension,
  // so supply one. Node runs the TypeScript directly via --experimental-strip-types.
  const resolved = path.extname(target) ? target : `${target}.ts`;

  return nextResolve(pathToFileURL(resolved).href, context);
}
