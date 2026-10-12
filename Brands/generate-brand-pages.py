#!/usr/bin/env python3
"""
LAXCC Brand Page Generator
Generates SEO-optimized brand pages for all brands.
Template based on stereo.html — Elementor-safe, scoped CSS, !important tags.
"""
import os

# ============================================================
# SKIP LIST — already have custom pages
# ============================================================
SKIP = {"710-labs", "stereo", "whoa", "boutiq", "zig-zag-2"}

# Hero images from Dutchie product catalog (500x500)
HERO_IMAGES = {}
_img_tsv = os.path.join(os.path.dirname(os.path.abspath(__file__)), "brand-hero-images.tsv")
if os.path.exists(_img_tsv):
    with open(_img_tsv) as _f:
        for _line in _f.readlines()[1:]:
            _parts = _line.strip().split("\t")
            if len(_parts) == 2:
                HERO_IMAGES[_parts[0]] = _parts[1]

# ============================================================
# CATEGORY TEMPLATES
# ============================================================
CATEGORIES = {
    "flower": {
        "product_type": "Flower",
        "h1_type": "Premium Flower",
        "meta_products": "flower, buds & pre-rolls",
        "stats": [
            ("30%", "Up to THC"),
            ("100%", "Lab Tested"),
            ("CA", "Cultivated"),
            ("3+", "Strains Stocked"),
        ],
        "about": [
            ("Premium Cultivation", "Grown with care in California using proven cultivation methods — optimized lighting, nutrients, and environmental controls for maximum trichome development and terpene expression."),
            ("Flavor-Forward Strains", "Carefully selected genetics that deliver on both flavor and potency. From fruity and sweet to earthy and gassy — every strain is chosen for a standout smoking experience."),
            ("High-Potency Flower", "Consistently testing at high THC levels with rich terpene profiles. Each batch is grown to deliver strong effects alongside bold flavor — no mids, no filler."),
            ("California Grown & Licensed", "Cultivated and distributed through California's licensed supply chain. Every gram is tracked, tested, and compliant — from seed to sale."),
        ],
        "education_title": "What Makes Premium Cannabis Flower",
        "education_intro": "Not all flower is created equal. Here's what separates top-shelf bud from everything else.",
        "education": [
            ("1", "Trichome Density", "More trichomes means more THC and terpenes. Premium flower is visibly frosty — that crystal coating is where the potency lives."),
            ("2", "Terpene Profile", "Flavor and effects come from terpenes, not just THC percentage. Quality flower has a loud, complex nose you can smell through the bag."),
            ("3", "Cure & Trim", "Proper curing preserves potency, smoothness, and flavor. A clean hand-trim with no excess leaf means a better burn."),
            ("4", "Lab Testing", "Every batch is tested for THC/CBD potency, pesticides, heavy metals, mold, and residual solvents. No guesswork."),
            ("5", "Proper Storage", "Airtight containers, cool temperatures, no direct light. Fresh flower smokes smoother and tastes better."),
        ],
        "faq3_q": "Is {brand} flower lab-tested?",
        "faq3_a": "Yes. All {brand} flower sold at LAXCC is lab-tested by a licensed California testing facility. Every batch is screened for THC/CBD potency, pesticides, heavy metals, mold, and residual solvents before it hits our shelves.",
    },
    "vape": {
        "product_type": "Vapes",
        "h1_type": "Vapes & Cartridges",
        "meta_products": "vape cartridges, pods & pens",
        "stats": [
            ("90%+", "Up to THC"),
            ("100%", "Lab Tested"),
            ("CA", "Licensed"),
            ("5+", "Options Stocked"),
        ],
        "about": [
            ("Clean Extraction", "Made using advanced extraction methods that preserve the plant's natural terpene and cannabinoid profile — no fillers, no cutting agents, no vitamin E acetate."),
            ("Strain-Specific Options", "Real cannabis-derived terpenes from real strains. Each cartridge or pod delivers a flavor and effect profile true to its strain origin."),
            ("Premium Hardware", "Quality ceramic coils and leak-proof designs for smooth, consistent hits. No burnt taste, no wasted oil, no heavy metal concerns."),
            ("California Licensed", "Manufactured and distributed through California's regulated supply chain. Every unit is tested for potency and purity before reaching the shelf."),
        ],
        "education_title": "What Makes a Quality Cannabis Vape",
        "education_intro": "Not all vapes are created equal. Here's what to look for when choosing a cartridge or pod.",
        "education": [
            ("1", "Clean Oil", "No fillers, no PG/VG, no vitamin E acetate. Quality vapes use pure cannabis oil with natural terpenes."),
            ("2", "Strain-Specific", "Real cannabis-derived terpenes, not synthetic flavoring. The flavor and effects should match the strain on the label."),
            ("3", "Hardware Quality", "Ceramic heating elements, medical-grade materials, leak-proof design. Bad hardware ruins good oil."),
            ("4", "Lab Testing", "Every batch tested for potency, pesticides, heavy metals, and residual solvents. Clean oil in a clean cartridge."),
            ("5", "Proper Storage", "Store upright, away from heat and direct sunlight. Extreme temperatures degrade oil quality and flavor."),
        ],
        "faq3_q": "Are {brand} vapes safe and lab-tested?",
        "faq3_a": "Yes. All {brand} vape products sold at LAXCC are lab-tested by a licensed California testing facility. Every batch is screened for potency, pesticides, heavy metals, residual solvents, and contaminants. No fillers or cutting agents.",
    },
    "edible": {
        "product_type": "Edibles",
        "h1_type": "Cannabis Edibles",
        "meta_products": "edibles, gummies & infused treats",
        "stats": [
            ("100mg", "Up to Per Pack"),
            ("100%", "Lab Tested"),
            ("CA", "Licensed"),
            ("5+", "Products Stocked"),
        ],
        "about": [
            ("Precise Dosing", "Every piece is dosed consistently so you know exactly what you're getting. Whether it's 5mg or 10mg per serving, the experience is predictable and reliable."),
            ("Premium Ingredients", "Made with quality ingredients and real cannabis extract — not synthetic cannabinoids. Great taste that happens to be infused, not infused products trying to taste good."),
            ("Multiple Options", "From gummies and chocolates to baked goods and mints — there's an edible format for every preference and occasion."),
            ("California Licensed", "Manufactured in a licensed California facility with strict quality controls. Every batch is lab-tested for accurate dosing, potency, and contaminants."),
        ],
        "education_title": "What to Know About Cannabis Edibles",
        "education_intro": "Edibles hit different than smoking. Here's what every customer should know before buying.",
        "education": [
            ("1", "Precise Dosing", "Quality edibles deliver consistent mg per piece. Look for brands that test every batch for accurate dosing — no hot spots, no surprises."),
            ("2", "Onset Time", "Edibles take 30-90 minutes to kick in because they're processed through your digestive system. Don't redose too soon."),
            ("3", "Duration", "Effects typically last 4-8 hours — much longer than smoking or vaping. Plan accordingly, especially if you're new."),
            ("4", "Start Low", "If you're new to edibles, begin with 2.5-5mg THC and wait at least 2 hours before taking more. You can always eat more, never less."),
            ("5", "Lab Testing", "Every batch is tested for THC/CBD accuracy, pesticides, and contaminants. What's on the label is what's in the package."),
        ],
        "faq3_q": "How strong are {brand} edibles?",
        "faq3_a": "Potency varies by product — most {brand} edibles range from 5mg to 10mg THC per serving, with packages up to 100mg total. Check the label for exact dosing. If you're new to edibles, start with a low dose (2.5-5mg) and wait at least 2 hours before taking more.",
    },
    "preroll": {
        "product_type": "Pre-Rolls",
        "h1_type": "Pre-Rolls",
        "meta_products": "pre-rolls, joints & blunts",
        "stats": [
            ("35%+", "Up to THC"),
            ("100%", "Lab Tested"),
            ("CA", "Hand-Rolled"),
            ("5+", "Options Stocked"),
        ],
        "about": [
            ("Quality Flower Inside", "Rolled with real cannabis flower — no trim, no shake, no floor sweepings. What's inside the paper is the same quality you'd buy in a jar."),
            ("Ready to Smoke", "No grinding, no rolling, no mess. Just light up and enjoy. Perfect for on-the-go or when you want zero prep work."),
            ("Consistent Burn", "Properly rolled for an even, slow burn from start to finish. No canoeing, no running, no relighting halfway through."),
            ("California Licensed", "Rolled in a licensed California facility and lab-tested for potency and purity. Every batch meets state compliance standards."),
        ],
        "education_title": "What Makes a Premium Pre-Roll",
        "education_intro": "A great pre-roll is more than just flower in a paper. Here's what separates the best from the rest.",
        "education": [
            ("1", "Whole Flower", "Premium pre-rolls use ground whole flower, not trim or shake. You can taste the difference — smoother, more flavorful, more potent."),
            ("2", "Even Burn", "Proper packing and quality papers ensure a consistent, slow burn. No uneven burns, no relighting required."),
            ("3", "Infused Options", "Some pre-rolls add live resin, kief, or diamonds for extra potency. Infused joints can hit 35%+ THC."),
            ("4", "Single-Strain", "The best pre-rolls use one strain, not a blend. You get the full flavor and effect profile of that specific cultivar."),
            ("5", "Lab Testing", "Every batch tested for potency, pesticides, and contaminants. What's on the label matches what's in the joint."),
        ],
        "faq3_q": "What's inside {brand} pre-rolls?",
        "faq3_a": "{brand} pre-rolls are made with quality cannabis flower, rolled in California licensed facilities. Every batch is lab-tested for THC potency and screened for pesticides, mold, and contaminants. Check individual product labels for strain-specific details and whether they're infused.",
    },
    "concentrate": {
        "product_type": "Concentrates",
        "h1_type": "Concentrates",
        "meta_products": "concentrates, live resin, wax & dabs",
        "stats": [
            ("90%+", "Up to THC"),
            ("100%", "Lab Tested"),
            ("CA", "Extracted"),
            ("5+", "Products Stocked"),
        ],
        "about": [
            ("Premium Extraction", "Made using advanced extraction techniques that capture the full spectrum of cannabinoids and terpenes from the source flower."),
            ("Terpene-Rich", "Preserving the plant's natural terpene profile means richer flavor and more nuanced effects compared to distillate-based products."),
            ("Multiple Textures", "From live resin and badder to sauce and diamonds — different consistencies for different dabbing preferences and setups."),
            ("California Licensed", "Extracted in licensed California facilities with strict quality controls. Every batch is lab-tested for potency, purity, and residual solvents."),
        ],
        "education_title": "Understanding Cannabis Concentrates",
        "education_intro": "Concentrates pack more punch per hit than flower. Here's what you need to know.",
        "education": [
            ("1", "Extraction Methods", "Solventless (ice water, heat press) vs. hydrocarbon (BHO, PHO). Solventless is prized for purity; hydrocarbon excels at preserving certain profiles."),
            ("2", "Live vs. Cured", "Live resin is made from fresh-frozen flower, preserving peak terpenes. Cured resin uses dried flower — different flavor, still potent."),
            ("3", "Potency", "Concentrates range from 60-90%+ THC. A little goes a long way — start with a small dab if you're new."),
            ("4", "Textures", "Badder, sauce, diamonds, rosin, shatter — each has a different consistency and dabbing experience. Try a few to find your preference."),
            ("5", "Lab Testing", "Every batch tested for cannabinoid content, residual solvents, pesticides, and contaminants. Clean concentrates are non-negotiable."),
        ],
        "faq3_q": "Are {brand} concentrates lab-tested?",
        "faq3_a": "Yes. All {brand} concentrates sold at LAXCC are tested by a licensed California lab for THC/CBD potency, residual solvents, pesticides, heavy metals, and microbial contaminants. Only clean, compliant products make it to our shelves.",
    },
    "drink": {
        "product_type": "Drinks",
        "h1_type": "Cannabis Drinks",
        "meta_products": "THC drinks, seltzers & beverages",
        "stats": [
            ("100mg", "Up to Per Bottle"),
            ("100%", "Lab Tested"),
            ("CA", "Licensed"),
            ("3+", "Flavors Stocked"),
        ],
        "about": [
            ("Fast-Acting Formula", "Most cannabis beverages use nano-emulsion technology for faster onset — typically 15-30 minutes vs. 60-90 for traditional edibles."),
            ("Precise Dosing", "Every can or bottle is dosed accurately so you know exactly how many mg of THC you're consuming per serving. Easy to control your experience."),
            ("Social & Sessionable", "Cannabis drinks are perfect for social settings — crack one open like you would a beer or seltzer. A smokeless, discreet way to enjoy cannabis."),
            ("California Licensed", "Manufactured in licensed California facilities. Every batch is lab-tested for potency accuracy and purity."),
        ],
        "education_title": "Cannabis Beverages 101",
        "education_intro": "THC drinks are one of the fastest-growing cannabis categories. Here's why people love them.",
        "education": [
            ("1", "Faster Onset", "Nano-emulsion tech makes THC water-soluble, so it absorbs faster — feel effects in 15-30 minutes instead of waiting an hour."),
            ("2", "Precise Dosing", "Exact mg per serving, clearly labeled. No guessing, no uneven dosing like some edibles."),
            ("3", "Low Calorie", "Most cannabis beverages are under 50 calories. Some are zero-calorie seltzers — a lighter alternative to alcohol."),
            ("4", "Social Format", "Designed for sharing and social settings. Crack a can, pass it around — no smoking required."),
            ("5", "Shorter Duration", "Effects typically last 2-4 hours — shorter than traditional edibles, making it easier to plan your evening."),
        ],
        "faq3_q": "How fast do {brand} drinks kick in?",
        "faq3_a": "Most {brand} cannabis beverages use nano-emulsion technology for faster absorption. You'll typically feel effects within 15-30 minutes — much faster than traditional edibles which can take 60-90 minutes. Effects usually last 2-4 hours.",
    },
    "accessory": {
        "product_type": "Accessories",
        "h1_type": "Smoking Accessories",
        "meta_products": "rolling papers, wraps & accessories",
        "stats": [
            ("Top", "Brand Quality"),
            ("100%", "Authentic"),
            ("In", "Stock Now"),
            ("5+", "Products Stocked"),
        ],
        "about": [
            ("Trusted Brand", "A recognized name in smoking accessories — quality materials, consistent products, and a reputation built on delivering what smokers actually want."),
            ("Premium Materials", "Made with quality materials for a clean, even burn. No harsh chemicals, no off-flavors — just a smooth smoking experience."),
            ("Wide Selection", "Multiple sizes, styles, and formats to match your rolling preference. From classic papers to specialty wraps, there's something for every smoker."),
            ("Always In Stock", "We keep our accessory wall stocked so you never have to make a separate trip. Grab your papers and your flower in one stop at LAXCC."),
        ],
        "education_title": None,  # skip for accessories
        "education_intro": None,
        "education": [],
        "faq3_q": "What {brand} products does LAXCC carry?",
        "faq3_a": "We stock a selection of {brand} smoking accessories at LAXCC. Availability varies — check with our budtenders for the current selection. We keep our accessory wall well-stocked so you can grab everything you need in one trip.",
    },
    "topical": {
        "product_type": "Wellness Products",
        "h1_type": "Cannabis Wellness Products",
        "meta_products": "topicals, tinctures & wellness products",
        "stats": [
            ("Full", "Spectrum"),
            ("100%", "Lab Tested"),
            ("CA", "Licensed"),
            ("3+", "Products Stocked"),
        ],
        "about": [
            ("Targeted Relief", "Cannabis topicals and tinctures deliver cannabinoids exactly where you need them — whether that's localized relief from a balm or whole-body effects from a tincture."),
            ("Multiple Formats", "From creams and balms to tinctures and patches — different delivery methods for different needs and preferences."),
            ("Quality Ingredients", "Made with quality cannabis extract and complementary natural ingredients. Formulated for effectiveness, not just marketing."),
            ("California Licensed", "Manufactured in licensed California facilities with strict quality standards. Every batch is lab-tested for potency and purity."),
        ],
        "education_title": "Cannabis Wellness Products Explained",
        "education_intro": "Cannabis wellness products offer an alternative to smoking. Here's what to know.",
        "education": [
            ("1", "Topicals", "Creams, balms, and lotions applied directly to the skin. Most won't get you high — they deliver localized cannabinoid relief."),
            ("2", "Tinctures", "Liquid extracts taken under the tongue for fast absorption. Effects in 15-30 minutes. Easy to dose precisely with a dropper."),
            ("3", "Patches", "Transdermal patches deliver cannabinoids through the skin over several hours. Consistent, long-lasting, discreet."),
            ("4", "Ratios", "Many wellness products come in CBD:THC ratios like 1:1 or 3:1. Higher CBD ratios offer relief with less psychoactive effect."),
            ("5", "Lab Testing", "Every batch tested for cannabinoid content, contaminants, and accurate dosing. What's on the label is what's in the product."),
        ],
        "faq3_q": "Will {brand} products get me high?",
        "faq3_a": "It depends on the product. {brand} topicals (creams, balms) generally don't produce psychoactive effects — they deliver localized relief. Tinctures and ingestible products with THC will produce psychoactive effects. Check the THC:CBD ratio on each product label to understand what to expect.",
    },
    "multi": {
        "product_type": "Products",
        "h1_type": "Cannabis Products",
        "meta_products": "flower, vapes, edibles & more",
        "stats": [
            ("Top", "CA Brand"),
            ("100%", "Lab Tested"),
            ("CA", "Licensed"),
            ("10+", "Products Stocked"),
        ],
        "about": [
            ("Full Product Lineup", "From flower and vapes to edibles and concentrates — a complete range of cannabis products under one trusted brand name."),
            ("Consistent Quality", "The same quality standards across every product category. Whether you're buying a cartridge or an eighth, you know what you're getting."),
            ("California Staple", "One of the most recognized cannabis brands in California. Trusted by dispensaries and customers across the state."),
            ("Innovation", "Constantly pushing the category forward with new products, strains, and formats. A brand that evolves with the market."),
        ],
        "education_title": "Why Brand Matters in Cannabis",
        "education_intro": "With hundreds of cannabis brands on the market, here's why choosing a trusted name matters.",
        "education": [
            ("1", "Consistency", "Good brands deliver the same quality every time. No surprises — your favorite product hits the same batch after batch."),
            ("2", "Lab Testing", "Reputable brands test every batch, not just the minimum. Potency, pesticides, heavy metals, solvents — all checked."),
            ("3", "Transparency", "Trusted brands list ingredients, test results, and sourcing info. If a brand hides details, that's a red flag."),
            ("4", "Product Safety", "Licensed brands follow state regulations for manufacturing, packaging, and labeling. No black-market shortcuts."),
            ("5", "Innovation", "Top brands invest in R&D — better extraction methods, new formats, improved hardware. You benefit from that investment."),
        ],
        "faq3_q": "Are {brand} products lab-tested?",
        "faq3_a": "Yes. All {brand} products sold at LAXCC are lab-tested by licensed California testing facilities. Every batch is screened for potency, pesticides, heavy metals, mold, and residual solvents. Only products that pass testing make it to our shelves.",
    },
}

# ============================================================
# BRAND DATABASE
# ============================================================
BRANDS = [
    {"name": "7 Leaves", "slug": "7-leaves", "uuid": "728b057b-e4d2-4485-be0e-1063b930dc42", "cat": "flower", "tagline": "California craft flower with bold flavors and reliable potency."},
    {"name": "Aether Fields", "slug": "aether-fields", "uuid": "70175080-ebc1-4928-9e5f-5dd2f49556e0", "cat": "flower", "tagline": "Thoughtfully cultivated California cannabis flower."},
    {"name": "Aims Horticulture", "slug": "aims-horticulture", "uuid": "5b240c06-a77b-4276-95cb-66fc886bf59c", "cat": "flower", "tagline": "Small-batch indoor flower grown with precision in California."},
    {"name": "Albert Einstone", "slug": "albert-einstone", "uuid": "27c21d7d-f548-4199-a83d-b71a39ad827d", "cat": "preroll", "tagline": "Infused pre-rolls that hit hard and burn smooth."},
    {"name": "American Weed Co.", "slug": "american-weed-co", "uuid": "1148dc1d-6fa1-4d43-949c-0257f74c14ce", "cat": "flower", "tagline": "Affordable, quality cannabis flower for everyday smokers."},
    {"name": "Bangers", "slug": "bangers", "uuid": "ced17061-3d8a-4573-98f5-798ffb53dff5", "cat": "preroll", "tagline": "Pre-rolls built for potency and flavor — no filler, no shortcuts."},
    {"name": "Big Pete's Treats", "slug": "big-petes-treats", "uuid": "f6f6b30a-49d4-4bbf-b9fa-775f6fc6d70c", "cat": "edible", "tagline": "Classic cannabis cookies and baked goods — California's favorite edible brand since 2009."},
    {"name": "Big Tree", "slug": "big-tree", "uuid": "a6d8352d-d94e-422e-a9e3-ab1ff3ae39ef", "cat": "flower", "tagline": "Premium California flower grown for quality and consistency."},
    {"name": "Birdies", "slug": "birdies", "uuid": "e1171f8f-a117-4fe8-82a3-48d9c3908d47", "cat": "preroll", "tagline": "Small-batch pre-rolls crafted with whole flower for a clean smoke."},
    {"name": "BLOOM", "slug": "bloom", "uuid": "1d682d79-2ceb-45dc-826a-0c702cc68f42", "cat": "vape", "tagline": "Award-winning cannabis vape cartridges with strain-specific live resin and distillate options."},
    {"name": "BREEZ", "slug": "breez", "uuid": "ac55ea87-955c-4a7b-9535-7d1868f7f670", "cat": "edible", "tagline": "Precision-dosed cannabis mints, tablets, and tinctures for controlled, consistent effects."},
    {"name": "Cali Creamery", "slug": "cali-creamery", "uuid": "8f758d71-ace5-4c21-ac6e-f933cb52c939", "cat": "edible", "tagline": "Cannabis-infused ice cream and frozen treats — California indulgence meets THC."},
    {"name": "Camino", "slug": "camino", "uuid": "d5a04b99-5ebf-4f65-b243-2d36ac5de7db", "cat": "edible", "tagline": "California's favorite cannabis gummies — fruit-forward flavors with curated effect blends by Kiva."},
    {"name": "Cann", "slug": "cann", "uuid": "d1ddb85a-e375-4aa0-ad29-9f20bae10e33", "cat": "drink", "tagline": "Low-dose cannabis social tonics — a refreshing, sessionable alternative to alcohol."},
    {"name": "Cannabiotix", "slug": "cannabiotix", "uuid": "505217fb-f296-4c6d-8b26-d74b46aa2b9a", "cat": "flower", "tagline": "Ultra-premium indoor flower and concentrates — one of California's most awarded cannabis brands."},
    {"name": "Cheech", "slug": "cheech", "uuid": "33fe8d01-551a-4332-8cc0-65b2ffcfd554", "cat": "accessory", "tagline": "Cheech Marin's official cannabis accessory line — glass, papers, and smoking essentials."},
    {"name": "CIZI", "slug": "cizi", "uuid": "68c0f43b-c6cb-407f-8008-a67f43301e81", "cat": "vape", "tagline": "Clean, potent cannabis vape cartridges made in California."},
    {"name": "Claybourne Co.", "slug": "claybourne-co", "uuid": "5369177f-42fc-47c8-8ff7-799e521b0429", "cat": "flower", "tagline": "California's #1 flower brand — premium buds, infused pre-rolls, and small-batch concentrates."},
    {"name": "Clone Goddess", "slug": "clone-goddess", "uuid": "cc54b2b6-dacf-49bf-88d7-cda128fddca5", "cat": "flower", "tagline": "Exceptional cannabis flower from expertly selected genetics."},
    {"name": "Cold Fire", "slug": "cold-fire", "uuid": "a5b831a7-9ac5-488e-b72f-537d3cd08914", "cat": "concentrate", "tagline": "Live resin and cured resin extracts — terpy concentrates and vape cartridges from California."},
    {"name": "CQ", "slug": "cq", "uuid": "d68c4f6a-32db-4b2b-a3c2-598a6f5f9d2a", "cat": "flower", "tagline": "Quality California cannabis flower at accessible prices."},
    {"name": "Cure Company", "slug": "cure-company", "uuid": "f9161c06-e8ba-48b8-a6c6-93c96453b3b8", "cat": "flower", "tagline": "Premium indoor cannabis flower from one of LA's most respected cultivation brands."},
    {"name": "Delighted", "slug": "delighted", "uuid": "44bfe537-7bbb-4a78-940c-227efb7fbac3", "cat": "flower", "tagline": "Craft cannabis flower cultivated for flavor, potency, and bag appeal."},
    {"name": "DOJA", "slug": "doja", "uuid": "3c5c6b66-43c9-429b-ae8c-5ab69006e6a5", "cat": "flower", "tagline": "Premium cannabis flower with a focus on exotic genetics and bold terpene profiles."},
    {"name": "Dompen", "slug": "dompen", "uuid": "5ec5f475-e696-4f61-afa7-d25d9b356e51", "cat": "vape", "tagline": "Effect-based cannabis vape pens — designed around how you want to feel, not just the strain name."},
    {"name": "Dr. Norm's", "slug": "dr-norms", "uuid": "1355ef2b-c6d3-4e85-b4f0-f680943bac75", "cat": "edible", "tagline": "Cannabis-infused cookies, brownies, and baked goods — classic comfort treats with precise THC dosing."},
    {"name": "Drops", "slug": "drops", "uuid": "edb40dc1-9b3c-43a6-8dc8-dc3aa37b1c4e", "cat": "edible", "tagline": "Cannabis gummies and edibles crafted for consistent dosing and great taste."},
    {"name": "El Blunto", "slug": "el-blunto", "uuid": "02310a6b-cbbf-4bed-a715-c08778511a4b", "cat": "preroll", "tagline": "Premium cannabis blunts wrapped in real tobacco leaf — slow-burning, hand-rolled, and potent."},
    {"name": "Element", "slug": "element", "uuid": "9fa4c6ba-798a-431e-b5f1-b92173921d99", "cat": "vape", "tagline": "Cannabis vape cartridges and concentrates from a trusted California extractor."},
    {"name": "Elements", "slug": "elements", "uuid": "9ca0f17f-90f5-4c08-9fe9-96a65bfbda9b", "cat": "accessory", "tagline": "Ultra-thin rice rolling papers — clean-burning, slow-smoking, and naturally made."},
    {"name": "Ember Valley", "slug": "ember-valley", "uuid": "0b7572c9-9dba-4606-9a98-94e8c411fccb", "cat": "flower", "tagline": "Small-batch indoor flower from Ember Valley — terpy, frosty, and consistently fire."},
    {"name": "Emerald Sky", "slug": "emerald-sky", "uuid": "bb70e53c-5160-41c7-8809-e7de80167a2d", "cat": "edible", "tagline": "Cannabis-infused chocolates and edibles crafted with premium ingredients."},
    {"name": "Featured Farms", "slug": "featured-farms", "uuid": "08d99836-f39f-43a1-b166-15027d63166c", "cat": "concentrate", "tagline": "Collaborative cannabis concentrates featuring top California cultivators and their best genetics."},
    {"name": "Froot", "slug": "froot", "uuid": "5f795974-685c-480e-a1ef-366cf0a5cf82", "cat": "edible", "tagline": "Cannabis gummies and chews in bold fruit flavors — California's fun, flavorful edible brand."},
    {"name": "Goldmine Gummies", "slug": "goldmine-gummies", "uuid": "fd27daf9-341e-49e1-a751-c8bb408555d5", "cat": "edible", "tagline": "Cannabis gummies with precise dosing and natural fruit flavors."},
    {"name": "Good Tide", "slug": "good-tide", "uuid": "c6349e33-3c1e-4c16-9bf8-00fa42d59469", "cat": "flower", "tagline": "Clean, quality cannabis flower cultivated on the California coast."},
    {"name": "Grasshoppers", "slug": "grasshoppers", "uuid": "50ce6902-1e08-441b-b4c2-b764a30619ed", "cat": "flower", "tagline": "Craft cannabis flower from California's grassroots growers."},
    {"name": "Green Dawg", "slug": "green-dawg", "uuid": "6aea8e30-8855-4ec6-a3ef-9d5d963b5a71", "cat": "flower", "tagline": "Exotic indoor flower and infused pre-rolls from one of LA's heaviest hitters."},
    {"name": "Habitat", "slug": "habitat", "uuid": "d7715bec-be5e-46c3-939a-041ac8c93f32", "cat": "flower", "tagline": "Sustainably grown cannabis flower with a focus on environmental responsibility."},
    {"name": "Heirbloom", "slug": "heirbloom", "uuid": "14d97760-1e60-42ca-8bb1-33ded3f6603b", "cat": "flower", "tagline": "Heirloom and exotic genetics grown with care in California."},
    {"name": "Highatus", "slug": "highatus", "uuid": "3fdc295c-cafd-46c6-a8a7-084704225d4a", "cat": "flower", "tagline": "Premium California flower for your next highatus — quality bud for quality downtime."},
    {"name": "Jeeter", "slug": "jeeter", "uuid": "d80165b1-f4b5-45ea-9071-f5f571b47e4d", "cat": "preroll", "tagline": "America's #1 pre-roll brand — infused joints, baby jeeters, and liquid diamonds in every California dispensary."},
    {"name": "KANHA", "slug": "kanha", "uuid": "18bb1389-9029-40dc-877e-cb1ec992bf3d", "cat": "edible", "tagline": "Award-winning cannabis gummies in bold fruit flavors — nano-infused for faster onset and precise dosing."},
    {"name": "Kingpen", "slug": "kingpen", "uuid": "ac1d9691-a426-4401-bc8a-cf0186dad693", "cat": "vape", "tagline": "Award-winning cannabis vape cartridges with strain-specific live resin — a California OG brand."},
    {"name": "Kingroll", "slug": "kingroll", "uuid": "20051ad6-39b2-4336-b39c-044d1432f9ac", "cat": "preroll", "tagline": "Premium cannabis pre-rolls packed with quality flower for a smooth, even burn."},
    {"name": "Kiva", "slug": "kiva", "uuid": "677de0df-da8a-4253-9a02-ff71a42ae0dc", "cat": "edible", "tagline": "California's most trusted edibles brand — precision-dosed chocolates, gummies, and mints since 2010."},
    {"name": "Koa Cannabis Co.", "slug": "koa-cannabis-co", "uuid": "d829ed6a-6842-4fb6-8cb6-43cc5867b1c0", "cat": "flower", "tagline": "Premium cannabis flower cultivated with aloha spirit and California sun."},
    {"name": "Kush Company", "slug": "kush-company", "uuid": "56118063-d13f-4c3d-95ad-7906866752b8", "cat": "flower", "tagline": "Classic and exotic cannabis flower strains from a trusted California cultivator."},
    {"name": "LA Family Farms", "slug": "la-family-farms", "uuid": "d9d88fb5-afc4-4e96-9691-54356c19341b", "cat": "flower", "tagline": "Family-grown cannabis flower from Los Angeles — local roots, premium quality."},
    {"name": "LA Kush", "slug": "la-kush", "uuid": "b9159cd3-dacd-44d9-8431-76e00ca03e4c", "cat": "flower", "tagline": "Indoor-grown Los Angeles cannabis flower — heavy OG genetics and exotic crosses from the home of kush."},
    {"name": "LAX Packs", "slug": "lax-packs", "uuid": "9ddb92cf-5638-43db-bbaf-0c15bc589d98", "cat": "preroll", "tagline": "LAXCC's house pre-roll brand — quality flower, rolled fresh, priced right."},
    {"name": "LAXCC", "slug": "laxcc", "uuid": "9e70069c-cb19-48c5-945a-c6e79fab357e", "cat": "multi", "tagline": "Our house brand — curated cannabis products from LA's favorite dispensary near LAX Airport."},
    {"name": "LEVEL", "slug": "level", "uuid": "f19d20ba-bd31-4a68-9117-20cc5665589b", "cat": "edible", "tagline": "Precision cannabis tablets and effect-based products — microdosing made easy with exact milligram control."},
    {"name": "Lift Tickets", "slug": "lift-tickets", "uuid": "22e232f6-36dc-4d7a-b899-c0f21bd36a31", "cat": "preroll", "tagline": "Infused pre-rolls dipped in concentrate and rolled in kief — designed for experienced smokers who want max potency."},
    {"name": "Lost Farm", "slug": "lost-farm", "uuid": "07851e89-60b2-4c1f-a791-172f3e3bdced", "cat": "edible", "tagline": "Live resin gummies and chews from the Kiva family — fruit-forward flavors infused with strain-specific live resin."},
    {"name": "Luigi", "slug": "luigi", "uuid": "084c5845-4b74-4c0e-aaee-7652b87cbee3", "cat": "preroll", "tagline": "Cannabis pre-rolls with quality flower and consistent craftsmanship."},
    {"name": "Lusty", "slug": "lusty", "uuid": "059c2471-6311-45cb-a612-973890ebe85f", "cat": "flower", "tagline": "Bold, flavorful cannabis flower grown for the adventurous smoker."},
    {"name": "Manzanita Naturals", "slug": "manzanita-naturals", "uuid": "e0ff29ef-bb41-4dcf-a9b8-e05b70dfc6f8", "cat": "topical", "tagline": "Cannabis topicals and wellness products made with natural California ingredients."},
    {"name": "Master Makers", "slug": "master-makers", "uuid": "8095aff6-84c5-4e15-9b69-5581208d4b25", "cat": "accessory", "tagline": "Quality smoking accessories and tools for the discerning cannabis consumer."},
    {"name": "Maven", "slug": "maven", "uuid": "7876adfb-2fed-4b1c-a219-dffb04d8e42f", "cat": "accessory", "tagline": "Premium lighters and smoking accessories — reliable tools for every session."},
    {"name": "Maven Genetics", "slug": "maven-genetics", "uuid": "168f8d00-fae4-4ba4-a59f-451d1ad5266b", "cat": "flower", "tagline": "Exotic genetics and premium indoor flower from one of California's top genetic hunters."},
    {"name": "Micro Bar", "slug": "micro-bar", "uuid": "98aba420-d4ad-4a05-9c40-57442a904157", "cat": "edible", "tagline": "Micro-dosed cannabis chocolate bars — low dose per piece for easy, controlled consumption."},
    {"name": "Nameless", "slug": "nameless", "uuid": "775dea2b-0341-4227-9faf-85780c1d1229", "cat": "flower", "tagline": "Let the flower speak for itself — premium genetics grown to their full potential."},
    {"name": "Nasha", "slug": "nasha", "uuid": "5a6c9437-6e8d-4d27-9ee3-da74ee71a652", "cat": "concentrate", "tagline": "Old-school hash and pressed concentrates — solventless tradition meets modern quality standards."},
    {"name": "Northern Harvest", "slug": "northern-harvest", "uuid": "baf6656f-f102-4cd5-82f8-e8464c35cef5", "cat": "flower", "tagline": "Northern California cannabis flower — quality genetics from the Emerald Triangle heritage."},
    {"name": "Not Your Father's Root Beer", "slug": "not-your-fathers-root-beer", "uuid": "05ff5bef-d1bf-4c5c-80b5-8fbf2fc92751", "cat": "drink", "tagline": "THC-infused root beer — the classic nostalgic flavor with a cannabis twist."},
    {"name": "OCB", "slug": "ocb", "uuid": "88ac0162-d7d6-47a8-a531-d924164f577f", "cat": "accessory", "tagline": "Premium rolling papers from France — ultra-thin, natural, and slow-burning since 1918."},
    {"name": "Pabst Labs", "slug": "pabst-labs", "uuid": "ceb8c1cb-73e4-41a8-8fc1-5766dfb9aebc", "cat": "drink", "tagline": "Cannabis-infused seltzers from the Pabst family — sessionable, refreshing, and precisely dosed."},
    {"name": "Pacific Stone", "slug": "pacific-stone", "uuid": "6708af15-e788-4b40-9cd5-b729167e21fd", "cat": "flower", "tagline": "Affordable, greenhouse-grown California flower — quality cannabis at prices that make sense."},
    {"name": "Papa & Barkley", "slug": "papa-barkley", "uuid": "c78b7047-7c5f-4e61-9bdf-c92e7bec302d", "cat": "topical", "tagline": "Cannabis wellness products — balms, tinctures, and topicals made with whole-plant, solventless extract."},
    {"name": "Paraphernalia", "slug": "paraphernalia", "uuid": "f8389097-72f0-48c5-98cb-a02ad7ef7e07", "cat": "accessory", "tagline": "Smoking accessories and tools for every type of cannabis consumer."},
    {"name": "PAX", "slug": "pax", "uuid": "ef9a19b5-e67a-43b0-8bea-bced1ad65697", "cat": "vape", "tagline": "The gold standard in cannabis vaporizers — sleek, smart, and app-connected pod systems."},
    {"name": "Petra", "slug": "petra", "uuid": "4c9f982e-1da5-4232-a18d-01d3c8e8e24d", "cat": "edible", "tagline": "Micro-dosed cannabis mints by Kiva — 2.5mg per piece for precise, discreet consumption."},
    {"name": "Planta", "slug": "planta", "uuid": "9a5deac8-f3bb-49b4-a532-df7452c69210", "cat": "flower", "tagline": "Clean, carefully cultivated cannabis flower from California."},
    {"name": "Plug Play", "slug": "plug-play", "uuid": "4f1e6e20-5ab7-409e-901a-262f4c9ec830", "cat": "vape", "tagline": "Premium cannabis vape pods with an iconic magnetic plug-and-play system — potent, flavorful, leak-proof."},
    {"name": "Presidential", "slug": "presidential", "uuid": "584b8301-b7e1-494d-9234-70886a1defd0", "cat": "preroll", "tagline": "Infused blunts and moonrock pre-rolls — cannabis royalty wrapped in premium tobacco leaf."},
    {"name": "Punch Extracts", "slug": "punch-extracts", "uuid": "95250b1b-2549-47ee-a92e-9c53f41a30e8", "cat": "concentrate", "tagline": "Solventless live rosin and hash rosin from some of California's best cultivators."},
    {"name": "RAW", "slug": "raw", "uuid": "0f86f255-9b97-407d-8d15-13b779b41b4b", "cat": "accessory", "tagline": "The world's most popular rolling papers — unrefined, additive-free, and naturally slow-burning."},
    {"name": "Raw Garden", "slug": "raw-garden", "uuid": "bbdf26e3-c442-4346-898a-9dc657ff3acb", "cat": "vape", "tagline": "Clean, single-source live resin cartridges and dabs — California's best-selling cannabis concentrate brand."},
    {"name": "Remz", "slug": "remz", "uuid": "90dd2493-2815-47bc-8a71-b1a611af25bd", "cat": "flower", "tagline": "Premium cannabis flower with bold genetics and strong bag appeal."},
    {"name": "Rockstar", "slug": "rockstar", "uuid": "2adf1f26-66f8-4524-a5ee-f24e5e803d4d", "cat": "flower", "tagline": "Hard-hitting cannabis flower that lives up to its name."},
    {"name": "Rove", "slug": "rove", "uuid": "daa99d8d-6b0b-4db5-8664-534d7d3868b1", "cat": "vape", "tagline": "Premium cannabis vape cartridges with real terpene profiles — clean oil, quality hardware."},
    {"name": "Royal Blunts", "slug": "royal-blunts", "uuid": "d021fa69-07e1-4213-aa06-07655ec9d001", "cat": "accessory", "tagline": "Flavored blunt wraps and rolling wraps — a wide selection of flavors for every preference."},
    {"name": "Scratch Baked", "slug": "scratch-baked", "uuid": "522ed464-0c16-401f-9829-23adbc3fc5d2", "cat": "edible", "tagline": "Cannabis-infused baked goods made from scratch with quality ingredients."},
    {"name": "SELFiES", "slug": "selfies", "uuid": "1dc45f47-4011-4beb-b2b6-81d6ac01b865", "cat": "edible", "tagline": "Fun, flavorful cannabis edibles that are made for sharing."},
    {"name": "SHERBINSKIS", "slug": "sherbinskis", "uuid": "e395c80f-c590-423c-8b18-669129a8f386", "cat": "flower", "tagline": "The original Sherbert strain creator — legendary genetics and premium flower from Mr. Sherbinski himself."},
    {"name": "Sluggers", "slug": "sluggers", "uuid": "8a36b114-ab9f-4fbd-a2c9-5d33e4a7a656", "cat": "preroll", "tagline": "5-packs of quality cannabis pre-rolls — the perfect format for sharing or stocking up."},
    {"name": "Smokiez Edibles", "slug": "smokiez-edibles", "uuid": "42fc93f4-b11c-4b69-8110-198027021ad4", "cat": "edible", "tagline": "Cannabis gummies in sour fruit flavors — precisely dosed, full-spectrum, and vegan-friendly."},
    {"name": "Sparkiez", "slug": "sparkiez", "uuid": "d18d3fe4-3032-4f4d-b122-94c5410f8c99", "cat": "edible", "tagline": "Cannabis edibles crafted for a sparkling, flavorful experience."},
    {"name": "St. Ides", "slug": "st-ides", "uuid": "9002435f-1106-405e-8ebd-f47ff0ad67eb", "cat": "drink", "tagline": "THC-infused shots and drinks — the iconic brand reimagined for cannabis."},
    {"name": "STIIIZY", "slug": "stiiizy", "uuid": "1cebe6f5-bab2-4f15-a50a-9730fee79de4", "cat": "multi", "tagline": "California's most popular cannabis brand — pods, flower, edibles, and concentrates in every dispensary."},
    {"name": "Ted's Budz", "slug": "teds-budz", "uuid": "dac8b98e-084d-417f-b812-d534c9cf4e88", "cat": "flower", "tagline": "Exotic, high-potency indoor flower — Ted's Budz has some of the loudest strains in California."},
    {"name": "THC Design", "slug": "thc-design", "uuid": "61c55fcf-2d64-4e61-82e2-b730dd46826a", "cat": "flower", "tagline": "Award-winning indoor-grown cannabis flower designed from the ground up for maximum potency and flavor."},
    {"name": "THE TABLET", "slug": "the-tablet", "uuid": "82e56d8c-170d-4609-b1d0-0c819c8da057", "cat": "edible", "tagline": "Precision-dosed cannabis tablets for measured, consistent effects."},
    {"name": "Time Machine", "slug": "time-machine", "uuid": "a5936b7f-9ca5-4bff-b34c-4e671fe68e2a", "cat": "flower", "tagline": "Premium cannabis flower that takes your mind on a journey — exotic genetics with high potency."},
    {"name": "Top Shelf Cultivation", "slug": "top-shelf-cultivation", "uuid": "33f66fce-e622-471c-abca-3d694c067fb7", "cat": "flower", "tagline": "Top-shelf means top-shelf — premium indoor cannabis flower grown to the highest standards."},
    {"name": "Turn", "slug": "turn", "uuid": "ddf076f1-936e-4eb5-8d8d-0d726ff4a2b6", "cat": "vape", "tagline": "Premium cannabis vape cartridges with clean distillate and real cannabis terpenes."},
    {"name": "Turtle Pie", "slug": "turtle-pie", "uuid": "71aeccf6-408e-479c-8e38-88b842d638db", "cat": "flower", "tagline": "Exotic cannabis flower with dessert genetics — unique strains you won't find everywhere."},
    {"name": "Uncle Arnie's", "slug": "uncle-arnies", "uuid": "1e5c266e-9328-4472-8125-e6090fed2f0f", "cat": "drink", "tagline": "Cannabis-infused lemonade and fruit drinks — refreshing, nostalgic, and potent."},
    {"name": "West Coast Cure", "slug": "west-coast-cure", "uuid": "12bf51c6-d7f0-414c-997e-9c0af5c0f15d", "cat": "flower", "tagline": "OG California cannabis brand — premium indoor flower, pre-rolls, and concentrates since day one."},
    {"name": "Wyld", "slug": "wyld", "uuid": "d8a9a6d9-224c-440e-a408-bc56b5e3785c", "cat": "edible", "tagline": "Real-fruit cannabis gummies infused with broad-spectrum extract — the #1 edible brand in multiple states."},
    {"name": "Xotx", "slug": "xotx", "uuid": "f2a5ef2d-dc99-4511-9c6e-3f274f438092", "cat": "flower", "tagline": "Exotic cannabis flower with unique genetics and potent effects."},
    {"name": "Zig-Zag", "slug": "zig-zag", "uuid": "fd56a160-8273-490d-a63f-8ecd91a799f5", "cat": "accessory", "tagline": "The world's most iconic rolling paper brand — been rolling since 1879."},
]


META_TEMPLATES = {
    "flower":      "Buy {name} flower at LAXCC — licensed LA dispensary 5 min from LAX. Premium buds, lab-tested, taxes included. Walk in or order pickup today.",
    "vape":        "Shop {name} vapes at LAXCC — licensed LA dispensary 5 min from LAX. Lab-tested cartridges & pods, taxes included. Walk in or order pickup.",
    "edible":      "Buy {name} edibles at LAXCC — licensed LA dispensary 5 min from LAX. Gummies, chocolates & more, lab-tested. Walk in or order pickup today.",
    "preroll":     "Shop {name} pre-rolls at LAXCC — licensed LA dispensary 5 min from LAX. Joints & blunts, lab-tested, taxes included. Walk in or order pickup.",
    "concentrate": "Buy {name} concentrates at LAXCC — licensed LA dispensary 5 min from LAX. Live resin, wax & dabs, lab-tested. Walk in or order pickup today.",
    "drink":       "Shop {name} cannabis drinks at LAXCC — licensed LA dispensary 5 min from LAX. THC beverages, lab-tested, taxes included. Order pickup today.",
    "accessory":   "Buy {name} at LAXCC — licensed LA dispensary 5 min from LAX. Rolling papers, wraps & accessories always in stock. Walk in, open daily 9-9:30.",
    "topical":     "Shop {name} topicals at LAXCC — licensed LA dispensary 5 min from LAX. Balms, tinctures & wellness, lab-tested. Walk in or order pickup today.",
    "multi":       "Shop {name} at LAXCC — licensed LA dispensary 5 min from LAX. Flower, vapes, edibles & more, all lab-tested. Walk in or order pickup today.",
}


def generate_meta(brand):
    """Generate a meta description <=155 chars with local SEO + buying intent."""
    template = META_TEMPLATES[brand["cat"]]
    meta = template.format(name=brand["name"])
    # Truncate to 155 if needed (shouldn't happen with these templates)
    if len(meta) > 155:
        meta = meta[:152] + "..."
    return meta


TITLE_SHORT_TYPE = {
    "flower": "Flower",
    "vape": "Vapes",
    "edible": "Edibles",
    "preroll": "Pre-Rolls",
    "concentrate": "Concentrates",
    "drink": "Drinks",
    "accessory": "Accessories",
    "topical": "Wellness",
    "multi": "Cannabis",
}


TITLE_OVERRIDES = {
    "cq": "Buy CQ Premium Cannabis Flower Near LAX | LAXCC LA",
    "pax": "Shop PAX Vaporizers Near LAX | LAXCC LA Dispensary",
    "cizi": "Buy CIZI Vape Cartridges Near LAX | LAXCC LA CA",
    "rove": "Buy Rove Vape Cartridges Near LAX | LAXCC LA CA",
    "turn": "Buy Turn Vape Cartridges Near LAX | LAXCC LA CA",
}


def generate_title(brand):
    """Generate a title tag 50-55 chars. Cascade formats to hit the sweet spot."""
    if brand["slug"] in TITLE_OVERRIDES:
        return TITLE_OVERRIDES[brand["slug"]]
    name = brand["name"]
    short_type = TITLE_SHORT_TYPE[brand["cat"]]

    # Cascade — many options to hit 50-55 for any brand name length
    candidates = [
        f"Buy {name} {short_type} Near LAX | LAXCC",
        f"Buy {name} {short_type} Near LAX | LAXCC LA",
        f"Buy {name} {short_type} in LA Near LAX | LAXCC",
        f"Shop {name} {short_type} Near LAX Airport | LAXCC",
        f"Buy {name} {short_type} in Los Angeles | LAXCC",
        f"Shop {name} {short_type} in Los Angeles | LAXCC",
        f"Buy {name} {short_type} — LA Dispensary Near LAX",
        f"Buy {name} {short_type} — LAXCC LA Dispensary",
        f"Buy {name} {short_type} | LAXCC LA Dispensary",
        f"Buy {name} {short_type} | LAXCC Dispensary Near LAX",
        f"Buy {name} {short_type} | LA Dispensary Near LAX",
        f"Buy {name} {short_type} in LA | LAXCC Dispensary",
        f"Buy {name} {short_type} in Los Angeles Near LAX",
        f"Shop {name} {short_type} | LAXCC Los Angeles CA",
        f"Shop {name} {short_type} | LA Dispensary Near LAX",
        f"Shop {name} {short_type} in LA | LAXCC Dispensary",
        f"Buy {name} {short_type} in Los Angeles | LAXCC CA",
        f"Shop {name} {short_type} in Los Angeles Near LAX",
        f"Buy {name} {short_type} — Los Angeles Dispensary",
        f"Shop {name} {short_type} — Los Angeles Near LAX",
        f"Buy {name} {short_type} | LAXCC Los Angeles Dispensary",
        f"Shop {name} {short_type} | LAXCC Los Angeles Dispensary",
        f"Buy {name} {short_type} | LAXCC Los Angeles Dispensary CA",
        f"Shop {name} {short_type} | LAXCC Los Angeles CA Near LAX",
        f"Buy {name} {short_type} Near LAX | Los Angeles Dispensary",
        f"Shop {name} {short_type} Near LAX | LAXCC LA Dispensary CA",
        f"Buy {name} Cannabis {short_type} Near LAX | LAXCC LA",
        f"Shop {name} Cannabis {short_type} Near LAX | LAXCC LA",
        f"Buy {name} Cannabis {short_type} in LA | LAXCC Dispensary",
        f"Shop {name} Cannabis {short_type} | LAXCC LA Dispensary",
        f"Buy {name} Cannabis {short_type} in Los Angeles | LAXCC",
        f"Shop {name} Cannabis {short_type} | LA Near LAX | LAXCC",
        f"Buy {name} {short_type} Online | LAXCC LA Dispensary CA",
        f"Shop {name} {short_type} Online | LAXCC LA Dispensary CA",
        f"Buy {name} Premium {short_type} Near LAX | LAXCC LA CA",
        f"Shop {name} Premium {short_type} Near LAX | LAXCC LA CA",
        f"Buy {name} Premium {short_type} in LA | LAXCC Near LAX",
        f"Shop {name} Premium {short_type} in LA | LAXCC Near LAX",
    ]

    # First pass: find one in 50-55
    for c in candidates:
        if 50 <= len(c) <= 55:
            return c

    # Second pass: find closest to 52 that's <=55
    valid = [(c, abs(len(c) - 52)) for c in candidates if len(c) <= 55]
    if valid:
        valid.sort(key=lambda x: x[1])
        return valid[0][0]

    # Fallback: truncate the shortest candidate that's over 55
    over = [c for c in candidates if len(c) > 55]
    if over:
        return over[0][:55]

    return candidates[0][:55]


def safe_slug(slug):
    """CSS class names can't start with a digit. Prefix if needed."""
    if slug[0].isdigit():
        return f"brand-{slug}"
    return slug


def css_prefix(slug):
    return f"laxcc-{safe_slug(slug)}"


def generate_css(slug):
    p = css_prefix(slug)
    slug = safe_slug(slug)  # Use CSS-safe slug for all class names
    return f"""<style>
    /* ========================================
       {slug.upper()} BRAND PAGE — LAXCC
       Scoped to .{p}
       Elementor-safe: all rules use !important
       ======================================== */

    .{p} *,
    .{p} *::before,
    .{p} *::after {{
        box-sizing: border-box !important;
        margin: 0 !important;
        padding: 0 !important;
    }}

    .{p} {{
        --navy: #1D508A;
        --teal: #00AFC9;
        --orange: #F89459;
        --green: #7ECBB3;
        --cream: #FBF8F2;
        --white: #ffffff;
        --text: #1e293b;
        --text-muted: #64748b;
        --border: #e2e8f0;
        --navy-deep: #0f3a5f;

        font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        background: var(--cream) !important;
        color: var(--text) !important;
        line-height: 1.6 !important;
        overflow-x: hidden !important;
        -webkit-font-smoothing: antialiased !important;
        -moz-osx-font-smoothing: grayscale !important;
    }}

    @keyframes {p}-fadeUp {{
        from {{ opacity: 0; transform: translateY(24px); }}
        to   {{ opacity: 1; transform: translateY(0); }}
    }}

    /* HERO — mobile-first */
    .{p} .{slug}-hero {{ background: var(--cream) !important; }}

    .{p} .{slug}-hero-img {{
        width: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #ffffff !important;
        padding: 40px 20px !important;
        overflow: hidden !important;
    }}

    .{p} .{slug}-hero-img img {{
        max-width: 400px !important;
        width: 100% !important;
        height: auto !important;
        object-fit: contain !important;
        display: block !important;
    }}

    /* Gallery photo fill mode */
    .{p} .{slug}-hero-img-fill {{
        width: 100% !important;
        overflow: hidden !important;
        background: none !important;
        padding: 0 !important;
    }}

    .{p} .{slug}-hero-img-fill img {{
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        display: block !important;
    }}

    .{p} .{slug}-hero-body {{
        max-width: 1400px !important;
        margin: 0 auto !important;
        padding: 32px 20px 28px !important;
    }}

    .{p} .{slug}-hero-badge {{
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        background: none !important;
        border: none !important;
        color: var(--text-muted) !important;
        font-size: 0.65rem !important;
        font-weight: 700 !important;
        letter-spacing: 2px !important;
        text-transform: uppercase !important;
        padding: 6px 14px !important;
        border-radius: 100px !important;
        margin-bottom: 16px !important;
        white-space: nowrap !important;
        width: fit-content !important;
    }}

    .{p} .{slug}-hero-badge i {{ font-size: 0.55rem !important; }}

    .{p} .{slug}-hero h1 {{
        font-size: 2.4rem !important;
        font-weight: 900 !important;
        line-height: 1 !important;
        letter-spacing: -0.03em !important;
        color: var(--navy) !important;
        margin-bottom: 16px !important;
    }}

    .{p} .{slug}-hero h1 span {{ color: var(--orange) !important; }}

    .{p} .{slug}-hero-sub {{
        font-size: 0.95rem !important;
        color: var(--text-muted) !important;
        line-height: 1.6 !important;
        margin-bottom: 24px !important;
        max-width: 520px !important;
    }}

    .{p} .{slug}-hero-actions {{
        display: flex !important;
        flex-direction: column !important;
        gap: 10px !important;
        margin-bottom: 28px !important;
    }}

    .{p} .{slug}-hero-stats {{
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 12px !important;
        border-top: 1px solid var(--border) !important;
        padding-top: 24px !important;
    }}

    .{p} .{slug}-stat-val {{
        font-size: 1.5rem !important;
        font-weight: 900 !important;
        color: var(--orange) !important;
        letter-spacing: -0.03em !important;
        line-height: 1 !important;
        margin-bottom: 2px !important;
    }}

    .{p} .{slug}-stat-lbl {{
        font-size: 0.6rem !important;
        font-weight: 700 !important;
        color: var(--text-muted) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
    }}

    /* Buttons */
    .{p} .{slug}-btn-primary,
    .{p} a.{slug}-btn-primary,
    .{p} a.{slug}-btn-primary:link,
    .{p} a.{slug}-btn-primary:visited {{
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 10px !important;
        background: var(--orange) !important;
        color: #fff !important;
        font-size: 0.85rem !important;
        font-weight: 800 !important;
        letter-spacing: 0.06em !important;
        text-transform: uppercase !important;
        padding: 16px 32px !important;
        border-radius: 12px !important;
        text-decoration: none !important;
        white-space: nowrap !important;
        transition: all 0.3s ease !important;
        border: none !important;
        cursor: pointer !important;
    }}

    .{p} a.{slug}-btn-primary:hover {{
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 32px rgba(248, 148, 89, 0.4) !important;
    }}

    .{p} .{slug}-btn-secondary,
    .{p} a.{slug}-btn-secondary,
    .{p} a.{slug}-btn-secondary:link,
    .{p} a.{slug}-btn-secondary:visited {{
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 10px !important;
        background: transparent !important;
        color: var(--navy) !important;
        font-size: 0.85rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.04em !important;
        text-transform: uppercase !important;
        padding: 16px 32px !important;
        border-radius: 12px !important;
        text-decoration: none !important;
        white-space: nowrap !important;
        border: 1px solid var(--navy) !important;
        transition: all 0.3s ease !important;
        cursor: pointer !important;
    }}

    .{p} a.{slug}-btn-secondary:hover {{
        border-color: var(--orange) !important;
        color: var(--orange) !important;
        background: rgba(248, 148, 89, 0.06) !important;
    }}

    /* Desktop hero */
    @media (min-width: 769px) {{
        .{p} .{slug}-hero {{
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            min-height: 100vh !important;
        }}
        .{p} .{slug}-hero-img {{
            height: 100% !important;
            min-height: 100vh !important;
            border-radius: 0 0 0 32px !important;
            order: 2 !important;
            padding: 60px 40px !important;
        }}
        .{p} .{slug}-hero-img img {{ max-width: 480px !important; }}
        .{p} .{slug}-hero-img-fill {{
            height: 100% !important;
            min-height: 100vh !important;
            border-radius: 0 0 0 32px !important;
            order: 2 !important;
            padding: 0 !important;
        }}
        .{p} .{slug}-hero-img-fill img {{ max-width: none !important; }}
        .{p} .{slug}-hero-body {{
            order: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
            padding: 80px 60px !important;
        }}
        .{p} .{slug}-hero h1 {{ font-size: clamp(2.8rem, 4vw, 4.2rem) !important; margin-bottom: 24px !important; }}
        .{p} .{slug}-hero-sub {{ font-size: 1.05rem !important; margin-bottom: 36px !important; }}
        .{p} .{slug}-hero-actions {{ flex-direction: row !important; margin-bottom: 0 !important; }}
        .{p} .{slug}-hero-stats {{ margin-top: 48px !important; padding-top: 32px !important; }}
        .{p} .{slug}-stat-val {{ font-size: 2rem !important; }}
        .{p} .{slug}-stat-lbl {{ font-size: 0.7rem !important; }}
    }}

    /* ABOUT section */
    .{p} .{slug}-about {{ padding: 24px 0 0 !important; background: var(--cream) !important; }}
    .{p} .{slug}-about-container {{ max-width: 1400px !important; margin: 0 auto !important; padding: 0 20px !important; }}
    .{p} .{slug}-about-header {{ max-width: 680px !important; }}

    .{p} .{slug}-section-label {{
        display: inline-block !important;
        font-size: 0.7rem !important;
        font-weight: 800 !important;
        letter-spacing: 3px !important;
        text-transform: uppercase !important;
        color: var(--orange) !important;
        margin-bottom: 20px !important;
        position: relative !important;
        padding-left: 36px !important;
    }}
    .{p} .{slug}-section-label::before {{
        content: '' !important;
        position: absolute !important;
        left: 0 !important;
        top: 50% !important;
        width: 24px !important;
        height: 2px !important;
        background: var(--orange) !important;
    }}

    .{p} .{slug}-about-top {{ display: flex !important; align-items: flex-end !important; justify-content: space-between !important; gap: 24px !important; margin-bottom: 16px !important; }}
    .{p} .{slug}-about-header h2 {{ font-size: clamp(2rem, 3.5vw, 3rem) !important; font-weight: 900 !important; color: var(--navy) !important; letter-spacing: -0.03em !important; line-height: 1.05 !important; margin-bottom: 12px !important; }}
    .{p} .{slug}-about-header p {{ font-size: 1rem !important; color: var(--text-muted) !important; line-height: 1.6 !important; max-width: 480px !important; }}

    .{p} .{slug}-scroll-arrows {{ display: flex !important; gap: 8px !important; flex-shrink: 0 !important; }}
    .{p} .{slug}-scroll-arrow {{
        width: 40px !important; height: 40px !important; border-radius: 50% !important;
        border: 1px solid var(--border) !important; background: var(--white) !important;
        display: flex !important; align-items: center !important; justify-content: center !important;
        cursor: pointer !important; transition: all 0.2s ease !important;
        color: var(--text) !important; font-size: 0.8rem !important;
    }}
    .{p} .{slug}-scroll-arrow:hover {{ border-color: var(--navy) !important; color: var(--navy) !important; }}

    .{p} .{slug}-about-track {{
        display: flex !important; gap: 20px !important; overflow-x: auto !important;
        scroll-snap-type: x mandatory !important; -webkit-overflow-scrolling: touch !important;
        scrollbar-width: none !important; padding-bottom: 4px !important;
    }}
    .{p} .{slug}-about-track::-webkit-scrollbar {{ display: none !important; }}
    .{p} .{slug}-about-slide {{
        flex: 0 0 280px !important; scroll-snap-align: start !important;
        padding: 16px 0 0 !important; border-top: 2px solid var(--border) !important;
        transition: border-color 0.3s ease !important;
    }}
    .{p} .{slug}-about-slide:hover {{ border-color: var(--navy) !important; }}
    .{p} .{slug}-about-slide-num {{ font-size: 0.7rem !important; font-weight: 700 !important; color: var(--text-muted) !important; letter-spacing: 0.5px !important; margin-bottom: 12px !important; }}
    .{p} .{slug}-about-slide h3 {{ font-size: 1.1rem !important; font-weight: 800 !important; color: var(--text) !important; letter-spacing: -0.01em !important; margin-bottom: 8px !important; }}
    .{p} .{slug}-about-slide p {{ font-size: 0.85rem !important; color: var(--text-muted) !important; line-height: 1.6 !important; }}
    @media (min-width: 769px) {{ .{p} .{slug}-about-slide {{ flex: 0 0 320px !important; }} }}

    /* EDUCATION section */
    .{p} .{slug}-education {{ padding: 24px 0 !important; background: var(--cream) !important; }}
    .{p} .{slug}-education-container {{ max-width: 1400px !important; margin: 0 auto !important; padding: 0 20px !important; }}
    .{p} .{slug}-education-top {{ display: flex !important; align-items: flex-end !important; justify-content: space-between !important; gap: 24px !important; margin-bottom: 16px !important; }}
    .{p} .{slug}-education-intro h2 {{ font-size: clamp(2rem, 3.5vw, 3rem) !important; font-weight: 900 !important; color: var(--navy) !important; letter-spacing: -0.03em !important; line-height: 1.05 !important; margin-bottom: 8px !important; }}
    .{p} .{slug}-education-intro p {{ font-size: 0.9rem !important; color: var(--text-muted) !important; line-height: 1.6 !important; max-width: 480px !important; }}
    .{p} .{slug}-education-track {{
        display: flex !important; gap: 20px !important; overflow-x: auto !important;
        scroll-snap-type: x mandatory !important; -webkit-overflow-scrolling: touch !important;
        scrollbar-width: none !important;
    }}
    .{p} .{slug}-education-track::-webkit-scrollbar {{ display: none !important; }}
    .{p} .{slug}-education-slide {{
        flex: 0 0 220px !important; scroll-snap-align: start !important;
        padding: 16px 0 0 !important; border-top: 2px solid var(--border) !important;
        text-align: center !important; transition: border-color 0.3s ease !important;
    }}
    .{p} .{slug}-education-slide:hover {{ border-color: var(--navy) !important; }}
    .{p} .{slug}-education-num {{ font-size: 2.5rem !important; font-weight: 900 !important; color: var(--orange) !important; line-height: 1 !important; margin-bottom: 6px !important; }}
    .{p} .{slug}-education-name {{ font-size: 0.9rem !important; font-weight: 700 !important; color: var(--navy) !important; margin-bottom: 6px !important; }}
    .{p} .{slug}-education-desc {{ font-size: 0.8rem !important; color: var(--text-muted) !important; line-height: 1.5 !important; }}
    @media (min-width: 769px) {{
        .{p} .{slug}-education {{ padding: 60px 0 !important; }}
        .{p} .{slug}-education-container {{ padding: 0 60px !important; }}
        .{p} .{slug}-education-slide {{ flex: 0 0 260px !important; }}
    }}

    /* FAQ */
    .{p} .{slug}-faq {{ padding: 140px 0 !important; background: var(--cream) !important; }}
    .{p} .{slug}-faq-container {{
        max-width: 1400px !important; margin: 0 auto !important; padding: 0 60px !important;
        display: grid !important; grid-template-columns: 380px 1fr !important;
        gap: 80px !important; align-items: start !important;
    }}
    .{p} .{slug}-faq-sidebar {{ position: sticky !important; top: 40px !important; }}
    .{p} .{slug}-faq-sidebar h2 {{ font-size: 3rem !important; font-weight: 900 !important; color: var(--navy) !important; letter-spacing: -0.03em !important; line-height: 1 !important; margin-bottom: 24px !important; }}
    .{p} .{slug}-faq-sidebar p {{ font-size: 1.05rem !important; color: var(--text-muted) !important; line-height: 1.7 !important; margin-bottom: 32px !important; }}
    .{p} .{slug}-faq-deco {{ width: 80px !important; height: 4px !important; background: var(--orange) !important; border-radius: 2px !important; }}
    .{p} .{slug}-faq-list {{ display: flex !important; flex-direction: column !important; }}
    .{p} .{slug}-faq-item {{ border-bottom: 1px solid var(--border) !important; padding: 32px 0 !important; }}
    .{p} .{slug}-faq-item:first-child {{ border-top: 1px solid var(--border) !important; }}
    .{p} .{slug}-faq-q {{
        font-size: 1.3rem !important; font-weight: 800 !important; color: var(--navy) !important;
        cursor: pointer !important; display: flex !important; align-items: center !important;
        justify-content: space-between !important; gap: 20px !important;
        list-style: none !important; transition: color 0.3s ease !important; padding: 0 !important;
    }}
    .{p} .{slug}-faq-q::-webkit-details-marker {{ display: none !important; }}
    .{p} .{slug}-faq-q:hover {{ color: var(--orange) !important; }}
    .{p} .{slug}-faq-q .{slug}-faq-toggle {{
        font-size: 1.3rem !important; font-weight: 300 !important; flex-shrink: 0 !important;
        transition: transform 0.3s ease !important; color: var(--text-muted) !important;
    }}
    .{p} .{slug}-faq-item[open] .{slug}-faq-toggle {{ transform: rotate(45deg) !important; color: var(--orange) !important; }}
    .{p} .{slug}-faq-a {{
        padding-top: 16px !important; font-size: 1rem !important; color: var(--text-muted) !important;
        line-height: 1.7 !important; max-width: 90% !important; animation: {p}-fadeUp 0.4s ease !important;
    }}

    /* CTA */
    .{p} .{slug}-cta {{
        padding: 120px 0 !important; background: var(--cream) !important;
        text-align: center !important; position: relative !important; overflow: hidden !important;
    }}
    .{p} .{slug}-cta::before {{
        content: '' !important; position: absolute !important; top: 50% !important; left: 50% !important;
        transform: translate(-50%, -50%) !important; width: 600px !important; height: 600px !important;
        background: radial-gradient(circle, rgba(248, 148, 89, 0.06) 0%, transparent 70%) !important;
        border-radius: 50% !important; pointer-events: none !important;
    }}
    .{p} .{slug}-cta-container {{ max-width: 700px !important; margin: 0 auto !important; padding: 0 40px !important; position: relative !important; z-index: 1 !important; }}
    .{p} .{slug}-cta h2 {{ font-size: clamp(2.5rem, 5vw, 4rem) !important; font-weight: 900 !important; color: var(--navy) !important; letter-spacing: -0.03em !important; margin-bottom: 20px !important; line-height: 1.05 !important; }}
    .{p} .{slug}-cta p {{ font-size: 1.1rem !important; color: var(--text-muted) !important; margin-bottom: 40px !important; line-height: 1.7 !important; }}
    .{p} .{slug}-cta-actions {{ display: flex !important; gap: 16px !important; justify-content: center !important; flex-wrap: wrap !important; }}
    .{p} .{slug}-cta-location {{ margin-top: 48px !important; padding-top: 32px !important; border-top: 1px solid rgba(29, 80, 138, 0.1) !important; }}
    .{p} .{slug}-cta-location p {{ font-size: 0.85rem !important; color: var(--text-muted) !important; margin-bottom: 0 !important; }}
    .{p} .{slug}-cta-location strong {{ color: var(--navy) !important; }}

    /* Responsive */
    @media (max-width: 1024px) {{
        .{p} .{slug}-about-container,
        .{p} .{slug}-education-container,
        .{p} .{slug}-faq-container {{ padding: 0 40px !important; }}
        .{p} .{slug}-faq-container {{ grid-template-columns: 1fr !important; gap: 48px !important; }}
        .{p} .{slug}-faq-sidebar {{ position: static !important; }}
    }}
    @media (max-width: 640px) {{
        .{p} .{slug}-education,
        .{p} .{slug}-faq {{ padding: 40px 0 !important; }}
        .{p} .{slug}-about-container,
        .{p} .{slug}-education-container,
        .{p} .{slug}-faq-container {{ padding: 0 24px !important; }}
        .{p} .{slug}-faq-sidebar h2 {{ font-size: 2.2rem !important; }}
        .{p} .{slug}-faq-q {{ font-size: 1.1rem !important; }}
        .{p} .{slug}-cta {{ padding: 80px 0 !important; }}
    }}

    /* Elementor override */
    .elementor-widget-container .{p} {{
        width: 100vw !important;
        position: relative !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
    }}
</style>"""


def generate_html(brand):
    raw_slug = brand["slug"]
    slug = safe_slug(raw_slug)  # CSS-safe slug for class names
    name = brand["name"]
    uuid = brand["uuid"]
    cat = CATEGORIES[brand["cat"]]
    tagline = brand["tagline"]
    p = css_prefix(raw_slug)

    # Gallery photos fill, product shots contain
    img_url = HERO_IMAGES.get(raw_slug, "")
    is_fill = "laxcc.com/wp-content" in img_url
    hero_img_class = f"{slug}-hero-img-fill" if is_fill else f"{slug}-hero-img"

    # Stats
    stats_html = ""
    for val, lbl in cat["stats"]:
        stats_html += f"""
                <div>
                    <div class="{slug}-stat-val">{val}</div>
                    <div class="{slug}-stat-lbl">{lbl}</div>
                </div>"""

    # About cards
    about_cards = ""
    all_about = cat["about"] + [
        ("Available at LAXCC", f"We carry {name} at our dispensary near LAX Airport — walk in at 8332 Lincoln Blvd or order ahead for pickup. New products rotate in regularly, so check the live menu.")
    ]
    for i, (title, desc) in enumerate(all_about):
        about_cards += f"""
                <div class="{slug}-about-slide">
                    <div class="{slug}-about-slide-num">{i+1:02d}</div>
                    <h3>{title}</h3>
                    <p>{desc}</p>
                </div>"""

    # Education section
    education_html = ""
    if cat["education_title"]:
        edu_slides = ""
        for num, title, desc in cat["education"]:
            edu_slides += f"""
                <div class="{slug}-education-slide">
                    <div class="{slug}-education-num">{num}</div>
                    <div class="{slug}-education-name">{title}</div>
                    <div class="{slug}-education-desc">{desc}</div>
                </div>"""

        education_html = f"""
    <!-- EDUCATION -->
    <section class="{slug}-education">
        <div class="{slug}-education-container">
            <div class="{slug}-education-top">
                <div class="{slug}-education-intro">
                    <span class="{slug}-section-label">Know Your Product</span>
                    <h2>{cat["education_title"]}</h2>
                    <p>{cat["education_intro"]}</p>
                </div>
                <div class="{slug}-scroll-arrows">
                    <button class="{slug}-scroll-arrow {slug}-education-arrow" data-dir="-1" aria-label="Previous"><i class="fas fa-arrow-left"></i></button>
                    <button class="{slug}-scroll-arrow {slug}-education-arrow" data-dir="1" aria-label="Next"><i class="fas fa-arrow-right"></i></button>
                </div>
            </div>
            <div class="{slug}-education-track">{edu_slides}
            </div>
        </div>
    </section>"""

    # FAQ
    faq3_q = cat["faq3_q"].format(brand=name)
    faq3_a = cat["faq3_a"].format(brand=name)

    # Build scroll config for JS
    scroll_configs = [
        f"{{ track: '.{slug}-about-track', btns: '.{slug}-about-arrow', step: 340 }}",
    ]
    if cat["education_title"]:
        scroll_configs.append(f"{{ track: '.{slug}-education-track', btns: '.{slug}-education-arrow', step: 240 }}")

    scroll_js = ",\n            ".join(scroll_configs)

    meta_desc = generate_meta(brand)
    title_tag = generate_title(brand)

    return f"""<!-- SEO META — paste into Yoast/RankMath for this page -->
<!-- Title: {title_tag} -->
<!-- Description: {meta_desc} -->

{generate_css(slug)}

<div class="{p}">

    <!-- HERO -->
    <section class="{slug}-hero">
        <div class="{hero_img_class}">
            <img src="{HERO_IMAGES.get(raw_slug, f'https://laxcc.com/wp-content/uploads/{raw_slug}-hero.webp')}"
                alt="{name} products available at LAXCC dispensary in Los Angeles near LAX Airport"
                loading="eager">
        </div>

        <div class="{slug}-hero-body">
            <div class="{slug}-hero-badge">
                <i class="fas fa-shield-halved"></i>
                Licensed Dispensary &bull; 5 Min From LAX
            </div>
            <h1>Buy {name} <span>{cat["h1_type"]}</span> in Los Angeles</h1>
            <p class="{slug}-hero-sub">
                {tagline} Shop {name} at LAXCC — a licensed Los Angeles dispensary just 5 minutes from LAX Airport. Available in-store and for pickup.
            </p>
            <div class="{slug}-hero-actions">
                <a href="#{raw_slug}-live-menu" class="{slug}-btn-primary">
                    Shop {name} <i class="fas fa-arrow-down"></i>
                </a>
                <a href="https://www.google.com/maps?daddr=8332+Lincoln+Blvd,+Los+Angeles,+CA+90045"
                    target="_blank" rel="noopener noreferrer" class="{slug}-btn-secondary">
                    <i class="fas fa-location-dot"></i> Get Directions
                </a>
            </div>
            <div class="{slug}-hero-stats">{stats_html}
            </div>
        </div>
    </section>

</div>
<!-- END scoped wrapper — shortcode lives OUTSIDE -->

<!-- LIVE MENU -->
<section id="{raw_slug}-live-menu">
    [leafbridge_specific_product_filter brand="{uuid}" count="20"]
</section>

<!-- Resume scoped wrapper -->
<div class="{p}">

    <!-- ABOUT -->
    <section class="{slug}-about">
        <div class="{slug}-about-container">
            <div class="{slug}-about-top">
                <div class="{slug}-about-header">
                    <span class="{slug}-section-label">About the Brand</span>
                    <h2>Why {name}</h2>
                    <p>{tagline}</p>
                </div>
                <div class="{slug}-scroll-arrows">
                    <button class="{slug}-scroll-arrow {slug}-about-arrow" data-dir="-1" aria-label="Previous"><i class="fas fa-arrow-left"></i></button>
                    <button class="{slug}-scroll-arrow {slug}-about-arrow" data-dir="1" aria-label="Next"><i class="fas fa-arrow-right"></i></button>
                </div>
            </div>
            <div class="{slug}-about-track">{about_cards}
            </div>
        </div>
    </section>
{education_html}

    <!-- FAQ -->
    <section class="{slug}-faq">
        <div class="{slug}-faq-container">
            <div class="{slug}-faq-sidebar">
                <h2>Common Questions</h2>
                <p>Everything you need to know about buying {name} at LAXCC near LAX Airport in Los Angeles.</p>
                <div class="{slug}-faq-deco"></div>
            </div>

            <div class="{slug}-faq-list">
                <details class="{slug}-faq-item">
                    <summary class="{slug}-faq-q">
                        Where can I buy {name} near LAX?
                        <span class="{slug}-faq-toggle">+</span>
                    </summary>
                    <div class="{slug}-faq-a">
                        LAXCC is a licensed Los Angeles dispensary at 8332 Lincoln Blvd, Los Angeles, CA 90045 — just 5 minutes from LAX Airport. We carry {name} {cat["meta_products"]}. Walk in or order ahead for pickup.
                    </div>
                </details>

                <details class="{slug}-faq-item">
                    <summary class="{slug}-faq-q">
                        What {name} products does LAXCC carry?
                        <span class="{slug}-faq-toggle">+</span>
                    </summary>
                    <div class="{slug}-faq-a">
                        We stock a curated selection of {name} {cat["meta_products"]}. Availability rotates with new drops — check our live menu above for the most current inventory and pricing.
                    </div>
                </details>

                <details class="{slug}-faq-item">
                    <summary class="{slug}-faq-q">
                        {faq3_q}
                        <span class="{slug}-faq-toggle">+</span>
                    </summary>
                    <div class="{slug}-faq-a">
                        {faq3_a}
                    </div>
                </details>

                <details class="{slug}-faq-item">
                    <summary class="{slug}-faq-q">
                        How much does {name} cost at LAXCC?
                        <span class="{slug}-faq-toggle">+</span>
                    </summary>
                    <div class="{slug}-faq-a">
                        Pricing varies by product and size — check the live menu on this page for current prices. LAXCC includes all taxes in our listed prices, so what you see is what you pay.
                    </div>
                </details>

                <details class="{slug}-faq-item">
                    <summary class="{slug}-faq-q">
                        What are LAXCC's hours and location?
                        <span class="{slug}-faq-toggle">+</span>
                    </summary>
                    <div class="{slug}-faq-a">
                        LAXCC is open daily from 9:00 AM to 9:30 PM at 8332 Lincoln Blvd in Westchester, Los Angeles — 5 minutes from LAX Airport and 8 minutes from the beach. Walk-ins welcome, no appointment needed. We also offer pickup, curbside, and drive-thru options.
                    </div>
                </details>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="{slug}-cta">
        <div class="{slug}-cta-container">
            <h2>Get {name} Today at LAXCC</h2>
            <p>
                Walk into our licensed dispensary near LAX Airport and shop {name} {cat["meta_products"]}. All products lab-tested and ready for you.
            </p>
            <div class="{slug}-cta-actions">
                <a href="https://www.google.com/maps?daddr=8332+Lincoln+Blvd,+Los+Angeles,+CA+90045"
                    target="_blank" rel="noopener noreferrer" class="{slug}-btn-primary">
                    <i class="fas fa-location-dot"></i> Directions to LAXCC
                </a>
            </div>
            <div class="{slug}-cta-location">
                <p><strong>LAXCC</strong> &bull; 8332 Lincoln Blvd, Los Angeles, CA 90045 &bull; Open Daily 9AM - 9:30PM</p>
            </div>
        </div>
    </section>

</div>

<script>
    (function () {{
        var ld = {{
            "@context": "https://schema.org",
            "@type": "Store",
            "name": "LAXCC - LAX Cannabis Club",
            "description": "Licensed dispensary near LAX Airport carrying {name} {cat["meta_products"]} in Los Angeles.",
            "url": "https://laxcc.com",
            "address": {{
                "@type": "PostalAddress",
                "streetAddress": "8332 Lincoln Blvd",
                "addressLocality": "Los Angeles",
                "addressRegion": "CA",
                "postalCode": "90045",
                "addressCountry": "US"
            }},
            "openingHours": "Mo-Su 09:00-21:30",
            "geo": {{
                "@type": "GeoCoordinates",
                "latitude": 33.9575,
                "longitude": -118.4178
            }}
        }};
        var s = document.createElement('script');
        s.type = 'application/ld+json';
        s.textContent = JSON.stringify(ld);
        document.head.appendChild(s);
    }})();

    /* Horizontal scroll arrows */
    (function () {{
        [
            {scroll_js}
        ].forEach(function (cfg) {{
            var track = document.querySelector('.{p} ' + cfg.track);
            if (!track) return;
            document.querySelectorAll('.{p} ' + cfg.btns).forEach(function (btn) {{
                btn.addEventListener('click', function () {{
                    var dir = parseInt(this.getAttribute('data-dir'));
                    track.scrollBy({{ left: dir * cfg.step, behavior: 'smooth' }});
                }});
            }});
        }});
    }})();
</script>
"""


# ============================================================
# GENERATE ALL PAGES
# ============================================================
if __name__ == "__main__":
    out_dir = os.path.dirname(os.path.abspath(__file__))
    generated = 0
    skipped = 0
    meta_rows = []

    for brand in BRANDS:
        if brand["slug"] in SKIP:
            skipped += 1
            continue

        filename = f"{brand['slug']}.html"
        filepath = os.path.join(out_dir, filename)
        html = generate_html(brand)

        with open(filepath, "w", encoding="utf-8") as f:
            f.write(html)

        cat = CATEGORIES[brand["cat"]]
        meta = generate_meta(brand)
        title = generate_title(brand)
        url = f"https://laxcc.com/brand/{brand['slug']}/"
        meta_rows.append((brand["slug"], brand["name"], url, title, meta, str(len(meta))))

        generated += 1
        print(f"  OK  {filename} ({len(meta)} chars)")

    # Write master TSV for Yoast/RankMath import
    tsv_path = os.path.join(out_dir, "brand-seo-meta.tsv")
    with open(tsv_path, "w", encoding="utf-8") as f:
        f.write("slug\tbrand\turl\ttitle_tag\tmeta_description\tchar_count\n")
        for row in meta_rows:
            f.write("\t".join(row) + "\n")

    print(f"\nDone! Generated {generated} brand pages, skipped {skipped}.")
    print(f"Master SEO file: {tsv_path}")
