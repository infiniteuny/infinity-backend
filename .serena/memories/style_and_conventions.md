# Style and Conventions

- **Preset:** The project uses the "laravel" code style preset enforced via Laravel Pint.
- **Specific Rules:**
  - `phpdoc_annotation_without_dot` is enabled.
  - `phpdoc_to_param_type` with `scalar_types` enabled.
  - `phpdoc_to_return_type` with `scalar_types` enabled.
- **Conventions:**
  - Standard Laravel conventions apply.
  - Use Eloquent models for database interaction.
  - Spatie Laravel Data objects for DTOs.
  - Return types and parameter types should be typed at the language level where possible, taking advantage of PHP 8.2 features.
