<?php
/**
 * Application and reference pages.
 *
 * Both sets are data-driven: one template renders any entry, so adding a page
 * is a new array key rather than a new file to keep in visual sync.
 */

require_once __DIR__ . '/site.php';

/**
 * Application pages — people search by what they are building
 * ("outdoor kitchen countertops dallas"), not by material name.
 *
 * `materials` must reference keys in SITE_MATERIALS; they drive the
 * recommendation cards and the links back into inventory.
 */
const APPLICATION_PAGES = [
    'countertops' => [
        'title' => 'Kitchen Countertops',
        'h1' => 'Kitchen Countertop Slabs',
        'meta' => 'Wholesale granite, quartzite, marble and quartz countertop slabs in Dallas, TX. Sold direct to fabricators, contractors and homeowners at slab-yard pricing.',
        'lead' => 'The kitchen counter is the hardest-working surface in the house. It takes heat, knives, wine, citrus and daily scrubbing — so the material has to be chosen for how the room is actually used, not just how it photographs.',
        'image' => 'countertopsk.jpeg',
        'materials' => ['quartzite', 'granite', 'quartz', 'marble'],
        'sections' => [
            ['What actually matters in a kitchen', 'Heat tolerance comes first: a pan straight off the burner will scorch the resin in engineered quartz, while granite shrugs it off. Porosity comes second — an unsealed light stone next to a coffee machine will show every splash within a month. Everything else is preference.'],
            ['Thickness and edge', 'Most kitchens run 3cm, which needs no plywood substrate and carries a mitred edge cleanly. 2cm is lighter and cheaper but usually wants build-up on exposed edges. Decide this before you select, because it changes which slabs are even available to you.'],
            ['Islands and seams', 'A run over about 120 inches will need a seam somewhere. Placing it well — and matching the veining across it — is what separates a good fabricator from a cheap one. If you want a waterfall island, tell us early: those need consecutive slabs from the same block, and we have to reserve them together.'],
        ],
        'faq' => [
            ['Which countertop material lasts longest?', 'Granite and quartzite both outlast the kitchen around them. Granite is the more forgiving of the two — quartzite sold loosely can sometimes be dolomitic marble, which etches.'],
            ['Can I put a hot pan directly on the counter?', 'On granite and quartzite, yes. On engineered quartz, never — the resin binder scorches and the mark is permanent. On marble, use a trivet.'],
            ['How much material do I need?', 'Bring your rough dimensions and we will work back to slab count, allowing for cut-outs and matching. Most standard kitchens land between two and three slabs.'],
        ],
    ],

    'bathroom-vanities' => [
        'title' => 'Bathroom Vanity Top Slabs',
        'h1' => 'Bathroom Vanity Slabs',
        'meta' => 'Marble, quartzite, quartz and porcelain vanity top slabs from our Dallas showroom. Sold as remnants or full slabs, sized right for single and double vanities.',
        'lead' => 'Vanities are where you can be braver. The surface takes far less abuse than a kitchen, the footprint is small, and a dramatic stone that would be overwhelming across ten feet of counter becomes the reason the room works.',
        'image' => 'gallerybathroom.jpeg',
        'materials' => ['marble', 'quartzite', 'quartz', 'porcelain'],
        'sections' => [
            ['This is where marble belongs', 'The usual objection to marble is etching, and in a kitchen that objection is fair. A vanity sees water, soap and toothpaste — not lemon juice and red wine. A honed finish hides what little marking does occur, and most clients come to like the patina.'],
            ['Remnants are your friend here', 'A vanity top rarely needs a full slab. Ask us about remnants and partial bundles: an exotic that would be out of budget across a kitchen is often very affordable at vanity size.'],
            ['Matching a full-height splash', 'If you want the stone running up the wall behind the mirror, say so when you select. Continuing the veining from the deck up the splash needs material cut from the same slab, and that changes how much we set aside.'],
        ],
        'faq' => [
            ['Will marble stain in a bathroom?', 'Rarely, if it is sealed and wiped down. Bathroom products are mostly pH-neutral. Cosmetics with strong dyes are the one thing worth wiping promptly.'],
            ['Can I use one slab for a double vanity?', 'Usually yes — a standard double vanity fits within a single slab, often with enough left for the splash.'],
        ],
    ],

    'outdoor-kitchens' => [
        'title' => 'Outdoor Kitchen Surfaces',
        'h1' => 'Outdoor Kitchen &amp; Patio Slabs',
        'meta' => 'UV-stable granite, porcelain and natural stone for Texas outdoor kitchens and patios. Built to resist fading, warping and freeze-thaw without cracking.',
        'lead' => 'A Texas summer is a brutal test. Direct sun, surface temperatures well over 120°F, and then a hard freeze in January. Most indoor materials fail this — choosing correctly outdoors is less about taste than about physics.',
        'image' => 'outdoork.jpeg',
        'materials' => ['granite', 'porcelain', 'natural-stone'],
        'sections' => [
            ['Never use engineered quartz outdoors', 'This is the single most expensive mistake in outdoor work. UV breaks down the resin that binds engineered quartz: the surface yellows, then warps, and no warranty covers it. Every reputable manufacturer states this explicitly.'],
            ['Granite and porcelain are the answers', 'Granite is dense, UV-stable and handles freeze-thaw without spalling. Large-format porcelain is completely non-porous, colour-stable in full sun, and its light weight makes it viable on structures that could not carry stone.'],
            ['Finish matters more outside', 'Leathered and honed finishes stay usable in direct sun, where a polished surface becomes a mirror and shows every water spot. Leathering also hides the dust that outdoor surfaces collect between cleans.'],
        ],
        'faq' => [
            ['Will granite fade in the Texas sun?', 'No. Granite is UV-stable — colour holds for decades outdoors.'],
            ['What about freezing?', 'Dense granite and porcelain handle freeze-thaw well. The risk with porous stone is water getting in, freezing and spalling the surface; sealing and correct drainage take care of that.'],
            ['Can porcelain span an outdoor island?', 'Yes, and its weight saving is often what makes the structure work. It needs a fabricator experienced with large-format porcelain — the cutting is unforgiving.'],
        ],
    ],

    'fireplace-surrounds' => [
        'title' => 'Fireplace Surrounds',
        'h1' => 'Fireplace Surround Slabs',
        'meta' => 'Bookmatched marble, quartzite and granite fireplace surround slabs in Dallas. Reserved as consecutive pairs for a seamless mirrored pattern on feature walls.',
        'lead' => 'A fireplace is a feature wall that happens to contain fire. It is the one surface in a house where the stone is purely decorative, which means you can choose for drama rather than durability.',
        'image' => 'FireplaceSurroundSlabs.jpeg',
        'materials' => ['marble', 'quartzite', 'natural-stone', 'granite'],
        'sections' => [
            ['Bookmatching is the whole point', 'Two consecutive slabs opened like a book give you a mirrored pattern that reads as one enormous piece of stone. On a full-height surround this is the difference between an expensive wall and a memorable one. It requires reserving consecutive slabs at purchase — they cannot be recreated later.'],
            ['Heat clearances', 'Natural stone handles radiant heat from a firebox without issue, but the clearances are set by the appliance manufacturer, not the stone. Check the firebox specification before finalising your design, and keep engineered quartz away from direct heat entirely.'],
            ['Going full height', 'A two-storey surround usually needs a four-way bookmatch and careful seam planning. Bring us the elevation drawing early — on this kind of work we often reserve a whole block rather than individual slabs.'],
        ],
        'faq' => [
            ['Can I use quartz around a fireplace?', 'Only well away from the firebox. Direct radiant heat scorches the resin. For anything close to the opening use natural stone.'],
            ['How far ahead should I plan a bookmatched wall?', 'As early as possible. Bookmatched pairs sell quickly and cannot be substituted — once the block is gone, that pattern no longer exists anywhere.'],
        ],
    ],

    'waterfall-islands' => [
        'title' => 'Waterfall Islands',
        'h1' => 'Waterfall Island Slabs',
        'meta' => 'Consecutive, lot-matched granite, quartzite, marble and quartz for waterfall kitchen islands in Dallas. Veining runs unbroken from the counter to the floor.',
        'lead' => 'A waterfall island turns the countertop down the sides to the floor. Done well the veining runs unbroken around the mitre and the island reads as a single carved block. Done badly the pattern jumps at the corner and the eye goes straight to it.',
        'image' => 'WaterfallIslandSlabs.jpeg',
        'materials' => ['quartzite', 'marble', 'quartz', 'granite'],
        'sections' => [
            ['It is a material problem before it is a fabrication problem', 'Continuous veining down the leg requires the leg to be cut from the slab immediately adjacent to the counter piece. That means buying consecutive slabs from the same block, together, at the point of purchase. No amount of skill at the saw fixes material bought a month apart.'],
            ['Budget for more than you measure', 'A waterfall consumes far more material than the counter area suggests, because the leg pieces have to come from specific positions in the slab. Expect an extra slab over a comparable flat island.'],
            ['Choose a stone with direction', 'Linear, directional veining — quartzites especially — produces the strongest waterfall effect. Busy, non-directional patterns lose the illusion entirely, so the effort buys you nothing.'],
        ],
        'faq' => [
            ['How many slabs does a waterfall island need?', 'Typically two to three for a standard island, depending on length and how much of the veining you want to preserve through the mitre.'],
            ['Can you match a waterfall to an existing counter?', 'Only if the original material is still available from the same lot. This is why we recommend buying the whole job at once.'],
        ],
    ],

    'backsplash' => [
        'title' => 'Full-Slab Backsplash',
        'h1' => 'Full-Slab Backsplash',
        'meta' => 'Full-slab backsplashes in Dallas, sawn from the same bundle as your countertop. Continuous veining, zero grout lines, in quartzite, marble, quartz, porcelain.',
        'lead' => 'A full-slab backsplash replaces tile with a single sheet of the same stone as the counter. No grout to scrub, no pattern fighting the counter, and the veining carries straight up the wall.',
        'image' => 'FullSlabBacksplash.jpeg',
        'materials' => ['quartzite', 'marble', 'quartz', 'porcelain'],
        'sections' => [
            ['Why people move away from tile', 'Grout behind a cooktop is the hardest surface in a kitchen to keep clean — it absorbs grease and discolours. A slab splash wipes down in one pass and has no joints to fail.'],
            ['Continuing the veining', 'The effect works because the splash is cut from the slab directly above the counter piece, so the pattern continues across the joint. Ask for this when you select, not after fabrication has started.'],
            ['Thinner is often better', 'Backsplashes carry no load, so 2cm — or 12mm porcelain — keeps the weight down and the reveal at the cabinet cleaner. It also stretches your material further.'],
        ],
        'faq' => [
            ['Does a slab backsplash cost more than tile?', 'The material costs more; the labour is usually less, and there is no ongoing grout maintenance. Over the life of a kitchen the gap narrows considerably.'],
            ['Can outlets be cut into it?', 'Yes. Plan positions before fabrication — cut-outs are made at the shop, not on site.'],
        ],
    ],

    'commercial-projects' => [
        'title' => 'Commercial Stone Supply',
        'h1' => 'Commercial &amp; Multi-Family Supply',
        'meta' => 'Bulk granite, quartz and porcelain supply for Texas hotels, apartments and multi-family builds. Block reservation and staged delivery keep every unit matching.',
        'lead' => 'Commercial work fails on consistency, not on price. Two hundred units that each look slightly different is a callback problem no discount recovers — which is why volume projects are bought differently from residential ones.',
        'image' => 'about-warehouse.jpg',
        'materials' => ['quartz', 'granite', 'porcelain', 'solid-surfaces'],
        'sections' => [
            ['Reserve blocks, not slabs', 'For anything that must match across many units we hold a whole block under your project name. Buying slab by slab as the build progresses guarantees drift — blocks sell out and the replacement never quite matches.'],
            ['Staged delivery', 'You should not be paying to store six months of stone on a jobsite. We hold material and release it in phases that follow your construction sequence.'],
            ['Specify for maintenance, not just looks', 'In hospitality the surface gets cleaned aggressively several times a day with whatever the crew has. Non-porous engineered quartz and porcelain survive that; a honed light marble in a restaurant will look tired within a year.'],
        ],
        'faq' => [
            ['Do you supply outside Dallas?', 'Yes — across Texas, with staged delivery sequenced to your build schedule.'],
            ['Can you hold pricing for a long project?', 'Pricing is held for the term agreed on the quote. For multi-phase work we set that out in writing at the start.'],
            ['Can you work from architectural specs?', 'Yes. Send the finish schedule and we will come back with material options, availability and lead times.'],
        ],
    ],
];

/** Reference pages — evergreen content that answers questions people search for. */
const REFERENCE_PAGES = [
    'glossary' => ['title' => 'Stone Glossary: Bookmatch, Vein Cut &amp; More', 'meta' => 'What bookmatching, vein cut, lot number and leathering actually mean — plain-English stone terms to know before you buy slabs in Dallas, TX.'],
    'care' => ['title' => 'How to Clean &amp; Seal Stone Countertops', 'meta' => 'How to clean granite, marble and quartz without etching, how often to reseal granite, and how to fix a marble etch mark — a care guide for every surface.'],
    'buying-guide' => ['title' => 'Countertop Buying Guide: What to Know', 'meta' => 'What to know before buying granite, marble or quartz countertops in Texas — material, thickness, cost per square foot and what to ask a fabricator.'],
    'finishes' => ['title' => 'Polished vs Honed vs Leathered Stone', 'meta' => 'Polished, honed, leathered, brushed and matte stone finishes compared — how each one looks, wears and which rooms and materials suit it best.'],
    'thickness' => ['title' => '2cm vs 3cm Countertop Thickness Guide', 'meta' => '2cm versus 3cm slabs, plus 6mm and 12mm porcelain, explained — what each thickness is for, whether 3cm is worth it, and how it changes cost.'],
    'compare' => ['title' => 'Granite vs Quartz vs Marble vs Quartzite', 'meta' => 'Granite, quartzite, marble, quartz, porcelain and solid surface compared side by side on hardness, heat resistance, porosity, outdoor use and price.'],
    'faq' => ['title' => 'Stone Slab FAQ: Pricing, Delivery &amp; Holds', 'meta' => 'Real questions on slab pricing, square footage per slab, holds, delivery and fabrication — answered plainly by Royale Surfaces in Dallas, TX.'],
];
