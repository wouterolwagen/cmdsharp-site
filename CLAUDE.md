# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a PHP-based marketing website for CmdSharp, an AI-powered invoicing application. The site is designed to showcase the product features, handle customer purchases, and provide download functionality with license validation.

## Architecture

### Core Components

- **Landing Page** (`index.html`): Single-page application with embedded CSS and JavaScript
- **Download System** (`download.php`): License validation and APK distribution
- **Success Page** (`success.php`): Post-purchase license generation and display
- **Legal Pages** (`terms.html`, `privacy.html`, `cancel.html`): Static legal content

### Key Features

1. **Dynamic Pricing**: Location-based pricing with PayFast (ZA) and LemonSqueezy (International)
2. **License System**: Validates and generates license keys via external API
3. **Professional Design**: Gradient backgrounds, responsive layout, professional styling
4. **Interactive Elements**: Smooth scrolling, animations, dynamic command demos

## Development

### File Structure
```
/
├── index.html          # Main landing page
├── download.php        # License validation and download
├── success.php         # Post-purchase success page
├── terms.html          # Terms and conditions
├── privacy.html        # Privacy policy
├── cancel.html         # Cancellation page
├── images/            # Static assets
│   ├── favicon.ico
│   ├── header.webp
│   └── logo-transparent.png
└── README.md          # Basic project info
```

### External Dependencies

- **API Endpoints**: 
  - `https://ai.proviska.com/api/get-location.php` - Location detection
  - `https://ai.proviska.com/api/pricing-config.php` - Pricing configuration
  - `https://ai.proviska.com/api/license-validate-v2.php` - License validation
  - `https://ai.proviska.com/api/generate-license.php` - License generation
  - `https://ai.proviska.com/api/app-update.php` - APK downloads

- **Third-party Services**:
  - PayFast (South African payments)
  - LemonSqueezy (International payments)
  - Tailwind CSS (for legal pages)

### Technology Stack

- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Backend**: PHP
- **Styling**: Custom CSS with gradients, animations, and responsive design
- **Payment Processing**: Integrated via external checkout system

## Content Management

### Pricing Updates
- Pricing is managed externally via API
- Location detection determines appropriate currency and payment gateway
- Button text and pricing display updates dynamically

### License System
- License keys follow format: `PREFIX-XXXX-XXXX-XXXX`
- Validation handled via external API
- Download access requires valid license

### Design System
- Brand colors: Primary gradient (#667eea to #304C67), Gold accents (#ffd700)
- Typography: Segoe UI font family
- Responsive breakpoints: 768px, 600px
- Animation: Fade-in effects and smooth transitions

## Testing

No automated testing framework is configured. Testing should focus on:
- Cross-browser compatibility
- Mobile responsiveness
- Payment flow validation
- License system functionality
- API endpoint connectivity

## Deployment

This is a static site with PHP components that should be deployed to a web server with PHP support. The site connects to external APIs for dynamic functionality.