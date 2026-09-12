import { register } from "node:module";

// Installs the "@/" resolver in ./alias-hooks.mjs. Loaded with `node --import`
// so the hook is in place before any test module is resolved.
register("./alias-hooks.mjs", import.meta.url);
