---
name: Node/npm environment for segue-me-web
description: How to run tsc, build and lint in this project given mixed Node versions
type: feedback
---

Use `/Users/reinaldo.ribeiro/.nvm/versions/node/v22.16.0/bin/node` directly to invoke project binaries.

For tsc: `/Users/reinaldo.ribeiro/.nvm/versions/node/v22.16.0/bin/node node_modules/.bin/tsc --noEmit`

For build: `PATH="/Users/reinaldo.ribeiro/.nvm/versions/node/v22.16.0/bin:$PATH" npm run build`

The `npm run lint` command (`next lint`) is broken in this environment — it reports "no such directory: .../segue-me-web/lint". This is an environment config issue unrelated to code changes. Use the build to validate instead.

**Why:** The system node is v14.18.2 which is incompatible with the installed npm version. The project actually needs Node 22.

**How to apply:** Always use the v22.16.0 binary path for any node/npm commands in segue-me-web.
