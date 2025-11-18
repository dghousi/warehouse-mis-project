import { readdirSync } from "fs";
import { join, extname } from "path";

function walk(dir, callback) {
  const entries = readdirSync(dir, { withFileTypes: true });
  for (const entry of entries) {
    const fullPath = join(dir, entry.name);
    if (entry.isDirectory()) {
      walk(fullPath, callback);
    } else if ([".ts", ".tsx"].includes(extname(entry.name))) {
      callback(fullPath);
    }
  }
}

function checkFilename(file) {
  const name = file.split("/").pop().replace(/\.(ts|tsx)$/, "");

  // ✅ Skip index files
  if (file.endsWith('/index.ts') || file.endsWith('/index.tsx')) {
    return;
  }

  const isHook = /src\/(modules\/[^/]+\/presentation\/hooks|hooks)\//.test(file);
  const isEntityOrInterface = /src\/(modules\/[^/]+\/domain\/(entities|interfaces)|types)\//.test(file);
  const isTest = /\.(test|spec)\.tsx$/.test(file);
  const isConfig = /src\/modules\/[^/]+\/presentation\/config\//.test(file);

  if (isHook) {
    // Hooks: camelCase with 'use' prefix (e.g., useAuth.ts, useMobile.ts)
    const hookRegex = /^use[A-Z][a-zA-Z0-9]*$/;
    if (!hookRegex.test(name)) {
      console.error(`❌ Bad hook file name: ${file} (must use camelCase with 'use' prefix, e.g., useAuth.ts)`);
      process.exitCode = 1;
    }
  } else if (isEntityOrInterface) {
    // Entities, Interfaces, Types: PascalCase (e.g., User.ts, AuthRepositoryInterface.ts)
    const pascalRegex = /^[A-Z][a-zA-Z0-9]*$/;
    if (!pascalRegex.test(name)) {
      console.error(`❌ Bad entity/interface/type file name: ${file} (must use PascalCase, e.g., User.ts)`);
      process.exitCode = 1;
    }
  } else if (isConfig) {
    // Config files: kebab-case (e.g., role-config.ts)
    const kebabRegex = /^[a-z0-9-]+$/;
    if (!kebabRegex.test(name)) {
      console.error(`❌ Bad config file name: ${file} (must use kebab-case, e.g., role-config.ts)`);
      process.exitCode = 1;
    }
  } else if (!isTest) {
    // Components, Use Cases, Repositories, Stores, Utilities: kebab-case (e.g., user-profile.tsx, create-user-use-case.ts)
    const kebabRegex = /^[a-z0-9-]+$/;
    if (!kebabRegex.test(name)) {
      console.error(`❌ Bad file name: ${file} (must use kebab-case, e.g., user-profile.tsx or create-user-use-case.ts)`);
      process.exitCode = 1;
    }
  }
}

walk("./src", checkFilename);

if (process.exitCode === 1) {
  console.error("Filename check failed. Please fix the reported issues.");
} else {
  console.log("✅ All filenames are valid.");
}