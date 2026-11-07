# Project Instructions

Run project commands from this repository's root directory.

## PHP Imports

- Import referenced classes, interfaces, traits, and enums with `use` instead of writing fully qualified names in PHP code or PHPDoc.
- When short class names collide, import their parent namespaces and qualify each class with a distinguishing namespace segment. Move the import higher if those segments also collide; do not use `as` to resolve name collisions. For example, use `use Vendor\Billing;` and `use Vendor\Shipping;`, then refer to `Billing\Service` and `Shipping\Service`.

## PHPDoc

Apply these rules to new and modified PHP code. Existing PHPDoc is inconsistent; follow `.editorconfig` for whitespace.

- Add PHPDoc to classes and methods. Do not add it to constants or properties solely for style.
- For methods, document every parameter, the declared return type, and exceptions the method can propagate with `@param`, `@return`, and `@throws`. Do not add filler descriptions.
- Order tags as `@param`, `@return`, then `@throws`. Keep parameter tags together and leave one blank PHPDoc line before a following `@return` or `@throws` group. Constructors have no `@return`.
- Import exception classes and use their short names in `@throws`. Do not manually align tag columns or parameter names; separate them with single spaces.
- Keep the closing `*/` directly above the documented declaration or attribute. Preserve intentional blank lines and wrap long PHPDoc lines when needed.
