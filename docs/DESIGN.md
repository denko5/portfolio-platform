# Design

Status: **Layouts and fonts approved** (Stage 3). Visual wireframes live in the Stage 3 design canvas; this file records the decisions.

## Direction
Premium dark with light "paper" sections and clean editorial typography. The teal and navy identity of the current site is the starting point.

## Colour
| Token | Value | Use |
|---|---|---|
| ink | `#080D1A` | page background |
| navy | `#0C1426` | alternate dark sections, nav |
| surface | `#101A30` | cards |
| text | `#EAF0FB` | body text |
| muted | `#A3AFC8` | secondary text |
| accent | `#2DD4BF` | one accent for buttons, links, highlights |
| accent ink | `#04121A` | text on accent |
| blue | `#5B8CFF` | data graphics only |
| paper | `#F3F6FB` | light sections (muted text `#46536F`) |

## Typography
- Headings: **Fraunces** (600). Body: **Inter** (400 and 600). Both self-hosted as variable or latin-subset files with `font-display: swap`. Load only the weights used.
- Fluid scale: h1 `clamp(2.05rem, 1rem + 4.3vw, 3.7rem)`, h2 `clamp(1.75rem, 1.15rem + 2.3vw, 2.8rem)`. Body 16 to 17px, line height 1.65, line length at most 62 characters.
- Optional monospace for small technical labels is not adopted for Release 1.

## Navigation
Floating pill: Home, About, Projects, Services, Insights (hidden until the first article), Contact, plus "Let's talk". Mobile: menu button opening a full-width panel with icons, 52px rows and the CV link. Overcend is not in the navigation.

## Pages
- **Home:** hero, featured projects (Legal Operations and Case Management Platform, E-Commerce, Hotel BI), services, experience, skills (text groups), insights (hidden if empty), contact, footer.
- **Projects:** search, category and technology filters, card grid, load more.
- **Project detail:** cover and gallery, at-a-glance panel, then Problem, Objectives, Role, Approach, Challenges, Outcomes, Lessons. Each section shows only when filled.
- **About, Contact, Privacy Policy, 404.**

## Responsive behaviour
Designed for 320, 375, 425, 768, 1024 and 1440px. Three-column grids become two, then one. Touch targets at least 44px. No horizontal scroll. Mobile layouts are designed, not shrunk.

## Components
Primary, secondary and ghost buttons, chips, pill nav, project card (16:10 image), service card, timeline entry, filter bar, form fields with visible labels and error text, dialog, status badge, placeholder tag, skip link, 3px focus ring. Radii 12, 20, 28 and pill. Motion: hover states and one hero entrance; reduced motion respected.

## Admin (Filament)
Sidebar groups: Content, Inbox, Site, Publishing. Dashboard: new enquiries, drafts waiting, last build status, Publish now. Project form tabs: Basics, Media (alt required, Placeholder toggle), Case study (all optional), Links and technologies, SEO. Header actions: Save draft, Preview, Publish. Settings: public contact email, notification email, phone, WhatsApp, social links, follower label, hero text, Overcend block (hidden toggle), privacy policy text. Guard: a project cannot be published with missing image alt text or a blocked screenshot.
