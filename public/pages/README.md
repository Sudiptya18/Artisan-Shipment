# Public Pages Directory

This directory mirrors the front-end structure used by the Vue SPA so that
standalone static assets (exported HTML, integration snippets, design system
documentation, etc.) have a predictable location.  

Suggested layout:

```text
public/pages/
  components/         # reusable UI snippets or rendered examples
  navigation/
    primary/          # top-level navigation sections
    secondary/        # nested navigation entries per section
  layouts/            # static layout previews
  products/
    create/           # exported form previews
    list/             # exported listing previews
  auth/               # authentication related pages
  errors/             # error state previews
```

The SPA runtime continues to mount from `resources/views/app.blade.php`, while
this tree can be used to host rendered artefacts needed by designers, QA, or
external integrations.

