# Phase 3: Advanced Schema & Structured Data

**Goal:** Expand schema support to match Rank Math's 20+ types.

This phase focuses on significantly increasing the number of supported schema types, enhancing the schema builder, and integrating specific schema-based blocks.

---

## 3.1: Additional Schema Types

The goal is to implement a wide range of new schema types to provide users with more options for rich results.

### Implementation Plan:
1.  **Video schema (VideoObject):**
    -   Fields: `name`, `description`, `thumbnailUrl`, `uploadDate`, `duration`, `contentUrl`, `embedUrl`.
    -   Integration: Add to post editor, detect video embeds (YouTube, Vimeo) to pre-fill data.
2.  **Course schema:**
    -   Fields: `courseCode`, `name`, `description`, `provider`, `hasCourseInstance`.
    -   Integration: New UI in the meta box for course details.
3.  **Software/App schema:**
    -   Fields: `name`, `operatingSystem`, `applicationCategory`, `aggregateRating`, `offers`.
    -   Integration: Meta box UI for software listings.
4.  **Book schema:**
    -   Fields: `name`, `author`, `isbn`, `bookEdition`, `bookFormat`.
    -   Integration: Add to post editor, potentially with ISBN lookup API.
5.  **Music schema (Album, Playlist):**
    -   Fields: `name`, `byArtist`, `numTracks`, `track`.
    -   Integration: Meta box UI for music-related content.
6.  **Movie schema:**
    -   Fields: `name`, `director`, `dateCreated`, `review`, `aggregateRating`.
    -   Integration: Meta box UI.
7.  **Restaurant schema:**
    -   Fields: `name`, `servesCuisine`, `priceRange`, `address`, `telephone`, `openingHours`.
    -   Integration: Enhance Local SEO module.
8.  **Service schema:**
    -   Fields: `name`, `serviceType`, `provider`, `areaServed`, `offers`.
    -   Integration: New meta box UI for service pages.
9.  **Job Posting schema:**
    -   Fields: `title`, `description`, `datePosted`, `validThrough`, `employmentType`, `hiringOrganization`, `jobLocation`, `baseSalary`.
    -   Integration: New meta box UI.
10. **Medical schema types:**
    -   Sub-types: `MedicalCondition`, `Drug`, `MedicalProcedure`.
    -   Integration: Specialized UI for medical content, ensuring compliance with health guidelines.

### Technical Requirements:
-   Each schema type will require a new class extending a base `Schema` class.
-   The new classes will be responsible for their own fields and JSON-LD output.
-   The UI will be built using React components and integrated into the existing meta box.

---

## 3.2: Schema Builder Enhancements

The goal is to make the schema builder more flexible and powerful.

### Implementation Plan:
1.  **Schema templates (reusable presets):**
    -   Allow users to save a configured schema as a template.
    -   Provide an interface to apply templates to posts.
2.  **Import schema from URL:**
    -   Add a tool to fetch a URL, find the schema on that page, and import it.
    -   Handle different schema formats (JSON-LD, Microdata).
3.  **Schema validation tool:**
    -   Integrate with the Schema.org validator or build a simple internal validator.
    -   Show errors and warnings to the user.
4.  **Multiple schemas per page:**
    -   Allow users to add more than one schema type to a single post.
    -   Ensure the JSON-LD output is a valid graph.
5.  **Conditional schema:**
    -   Add a UI for setting conditions (e.g., `post_type == 'post'`, `user_is == 'loggedIn'`).
    -   The schema will only be output if the conditions are met.
6.  **Custom schema code editor:**
    -   Provide a raw JSON-LD editor for advanced users.
    -   Include syntax highlighting and validation.

---

## 3.3: FAQ & HowTo Integration

The goal is to provide dedicated Gutenberg blocks for FAQ and How-to content with automatic schema generation.

### Implementation Plan:
1.  **FAQ block:**
    -   Create a new Gutenberg block with `Question` and `Answer` inner blocks.
    -   Automatically generate `FAQPage` schema.
    -   Provide styling options (accordion, plain list).
2.  **HowTo block:**
    -   Create a new Gutenberg block with `HowToStep` and `HowToSupply` inner blocks.
    -   Automatically generate `HowTo` schema.
    -   Include fields for total time, estimated cost, etc.
3.  **Import existing content:**
    -   Provide a tool to convert content from other FAQ plugins (e.g., Yoast FAQ block) into the new block.

---
