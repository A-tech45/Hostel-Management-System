from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import mm, cm
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle,
    PageBreak, HRFlowable, KeepTogether
)
from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_JUSTIFY
from reportlab.platypus import Flowable
from reportlab.pdfgen import canvas as pdfcanvas

# ── Colour Palette ──────────────────────────────────────────────────────────
DARK_BG     = colors.HexColor("#1a1a2e")
MID_BG      = colors.HexColor("#16213e")
ACCENT      = colors.HexColor("#e94560")
GOLD        = colors.HexColor("#f5a623")
TEAL        = colors.HexColor("#0f9b8e")
LIGHT_TEXT  = colors.HexColor("#e0e0e0")
MUTED       = colors.HexColor("#8888aa")
CARD_BG     = colors.HexColor("#0f3460")
CARD_BORDER = colors.HexColor("#e94560")
GRID_CELL   = colors.HexColor("#2a2a4a")
GRID_BORDER = colors.HexColor("#444466")
WHITE       = colors.white
GREEN       = colors.HexColor("#4caf50")
ORANGE      = colors.HexColor("#ff9800")
BLUE        = colors.HexColor("#2196f3")

W, H = A4

# ── Page background ──────────────────────────────────────────────────────────
def draw_background(c, doc):
    c.saveState()
    c.setFillColor(DARK_BG)
    c.rect(0, 0, W, H, fill=1, stroke=0)
    # subtle grid lines
    c.setStrokeColor(colors.HexColor("#ffffff10"))
    c.setLineWidth(0.3)
    for x in range(0, int(W)+1, 20):
        c.line(x, 0, x, H)
    for y in range(0, int(H)+1, 20):
        c.line(0, y, W, y)
    # top accent bar
    c.setFillColor(ACCENT)
    c.rect(0, H-6, W, 6, fill=1, stroke=0)
    # bottom bar
    c.setFillColor(TEAL)
    c.rect(0, 0, W, 4, fill=1, stroke=0)
    # page number
    c.setFillColor(MUTED)
    c.setFont("Helvetica", 8)
    c.drawCentredString(W/2, 10, f"Page {doc.page}")
    c.restoreState()

# ── Cover page ───────────────────────────────────────────────────────────────
def draw_cover(c, doc):
    c.saveState()
    # full background
    c.setFillColor(DARK_BG)
    c.rect(0, 0, W, H, fill=1, stroke=0)
    # decorative top stripe
    c.setFillColor(ACCENT)
    c.rect(0, H-10, W, 10, fill=1, stroke=0)
    # decorative bottom stripe
    c.setFillColor(TEAL)
    c.rect(0, 0, W, 10, fill=1, stroke=0)
    # big side accent
    c.setFillColor(colors.HexColor("#e9456015"))
    c.rect(0, 0, 20, H, fill=1, stroke=0)
    c.setFillColor(colors.HexColor("#e9456015"))
    c.rect(W-20, 0, 20, H, fill=1, stroke=0)

    # title block
    yc = H - 120
    c.setFillColor(CARD_BG)
    c.roundRect(40, yc - 160, W - 80, 180, 12, fill=1, stroke=0)
    c.setStrokeColor(ACCENT)
    c.setLineWidth(2)
    c.roundRect(40, yc - 160, W - 80, 180, 12, fill=0, stroke=1)

    c.setFillColor(ACCENT)
    c.setFont("Helvetica-Bold", 36)
    c.drawCentredString(W/2, yc - 10, "MACHINE COMPONENTS")
    c.setFillColor(GOLD)
    c.setFont("Helvetica-Bold", 22)
    c.drawCentredString(W/2, yc - 50, "The Ultimate Crafting Guide")
    c.setFillColor(LIGHT_TEXT)
    c.setFont("Helvetica", 13)
    c.drawCentredString(W/2, yc - 85, "Vanilla Redstone  •  Create Mod  •  Tech Essentials")
    c.setFillColor(MUTED)
    c.setFont("Helvetica", 10)
    c.drawCentredString(W/2, yc - 115, "Recipes · Crafting Grids · Usage Locations")

    # icon row (coloured squares as "blocks")
    colours = [ACCENT, GOLD, TEAL, GREEN, ORANGE, BLUE, ACCENT, GOLD]
    bx = 80
    for col in colours:
        c.setFillColor(col)
        c.roundRect(bx, yc - 220, 28, 28, 4, fill=1, stroke=0)
        bx += 38

    # description
    desc_y = yc - 270
    c.setFillColor(LIGHT_TEXT)
    c.setFont("Helvetica", 11)
    lines = [
        "This guide covers every essential component you need to build",
        "powerful machines in Minecraft. Each entry includes a full",
        "crafting recipe with a visual 3x3 grid, ingredients list,",
        "and a breakdown of where the component is used.",
    ]
    for line in lines:
        c.drawCentredString(W/2, desc_y, line)
        desc_y -= 18

    # sections preview
    sy = desc_y - 30
    sections = [
        ("⚙  SECTION 1", "Vanilla Redstone Components", ACCENT),
        ("🔩  SECTION 2", "Create Mod Components",        GOLD),
        ("🔧  SECTION 3", "Universal Machine Parts",       TEAL),
    ]
    for icon, name, col in sections:
        c.setFillColor(col)
        c.roundRect(60, sy - 4, W - 120, 26, 6, fill=0, stroke=1)
        c.setFillColor(col)
        c.setFont("Helvetica-Bold", 11)
        c.drawString(80, sy + 5, icon + "  " + name)
        sy -= 36

    c.restoreState()

# ── Craft-grid Flowable ───────────────────────────────────────────────────────
class CraftingGrid(Flowable):
    """Draw a 3x3 crafting grid with item labels."""
    CELL = 36

    def __init__(self, grid, result, result_label=""):
        super().__init__()
        self.grid = grid        # list of 9 strings, row-major
        self.result = result
        self.result_label = result_label
        self._w = self.CELL * 3 + self.CELL * 1.4 + self.CELL
        self._h = self.CELL * 3 + 10

    def wrap(self, aw, ah):
        return self._w, self._h

    def draw(self):
        c = self.canv
        S = self.CELL
        # grid
        for idx, item in enumerate(self.grid):
            row, col = divmod(idx, 3)
            x = col * S
            y = (2 - row) * S
            c.setFillColor(GRID_CELL)
            c.setStrokeColor(GRID_BORDER)
            c.setLineWidth(1)
            c.rect(x, y, S, S, fill=1, stroke=1)
            if item:
                c.setFillColor(WHITE)
                c.setFont("Helvetica", 6.5)
                words = item.split()
                if len(words) == 1:
                    c.drawCentredString(x + S/2, y + S/2 - 3, item)
                else:
                    c.drawCentredString(x + S/2, y + S/2 + 2, words[0])
                    c.drawCentredString(x + S/2, y + S/2 - 7, " ".join(words[1:]))

        # arrow
        ax = 3 * S + 8
        ay = S + S/2
        c.setStrokeColor(GOLD)
        c.setLineWidth(2)
        c.line(ax, ay, ax + S*0.7, ay)
        c.line(ax + S*0.7 - 6, ay + 5, ax + S*0.7, ay)
        c.line(ax + S*0.7 - 6, ay - 5, ax + S*0.7, ay)

        # result
        rx = ax + S*0.7 + 4
        ry = S
        c.setFillColor(ACCENT)
        c.setStrokeColor(GOLD)
        c.setLineWidth(1.5)
        c.rect(rx, ry, S, S, fill=1, stroke=1)
        if self.result:
            c.setFillColor(WHITE)
            c.setFont("Helvetica-Bold", 6.5)
            words = self.result_label.split() or self.result.split()
            if len(words) == 1:
                c.drawCentredString(rx + S/2, ry + S/2 - 3, words[0])
            else:
                c.drawCentredString(rx + S/2, ry + S/2 + 2, words[0])
                c.drawCentredString(rx + S/2, ry + S/2 - 7, " ".join(words[1:]))

# ── Styles ────────────────────────────────────────────────────────────────────
styles = getSampleStyleSheet()

def make_styles():
    return {
        "h1": ParagraphStyle("h1", fontSize=26, textColor=ACCENT,
                             fontName="Helvetica-Bold", spaceAfter=6,
                             spaceBefore=14, alignment=TA_LEFT),
        "h2": ParagraphStyle("h2", fontSize=17, textColor=GOLD,
                             fontName="Helvetica-Bold", spaceAfter=4,
                             spaceBefore=10),
        "h3": ParagraphStyle("h3", fontSize=13, textColor=TEAL,
                             fontName="Helvetica-Bold", spaceAfter=3,
                             spaceBefore=6),
        "body": ParagraphStyle("body", fontSize=10, textColor=LIGHT_TEXT,
                               fontName="Helvetica", leading=15,
                               spaceAfter=4, alignment=TA_JUSTIFY),
        "tag": ParagraphStyle("tag", fontSize=9, textColor=DARK_BG,
                              fontName="Helvetica-Bold", alignment=TA_CENTER),
        "small": ParagraphStyle("small", fontSize=8.5, textColor=MUTED,
                                fontName="Helvetica", leading=12),
        "section_intro": ParagraphStyle("section_intro", fontSize=11,
                                        textColor=MUTED, fontName="Helvetica-Oblique",
                                        leading=16, spaceAfter=10),
        "ingredient": ParagraphStyle("ingredient", fontSize=9.5, textColor=LIGHT_TEXT,
                                     fontName="Helvetica", leading=14, leftIndent=10),
        "uses_item": ParagraphStyle("uses_item", fontSize=9.5, textColor=GREEN,
                                    fontName="Helvetica", leading=14, leftIndent=10),
    }

ST = make_styles()

# ── Helper: coloured tag pill ─────────────────────────────────────────────────
def tag_table(text, bg=ACCENT):
    t = Table([[Paragraph(f"<b>{text}</b>", ST["tag"])]], colWidths=[90])
    t.setStyle(TableStyle([
        ("BACKGROUND", (0,0), (-1,-1), bg),
        ("ROUNDEDCORNERS", [5]),
        ("TOPPADDING",    (0,0),(-1,-1), 3),
        ("BOTTOMPADDING", (0,0),(-1,-1), 3),
    ]))
    return t

# ── Helper: section header bar ────────────────────────────────────────────────
def section_header(title, subtitle, colour=ACCENT):
    data = [[Paragraph(f"<font color='#{colour.hexval()[2:]}' size=18><b>{title}</b></font>",
                        ParagraphStyle("sh", fontSize=18, fontName="Helvetica-Bold",
                                       textColor=colour, alignment=TA_LEFT)),
             Paragraph(f"<font color='#8888aa' size=10><i>{subtitle}</i></font>",
                        ParagraphStyle("sh2", fontSize=10, fontName="Helvetica-Oblique",
                                       textColor=MUTED, alignment=TA_LEFT))]]
    t = Table(data, colWidths=[W*0.55, W*0.35])
    t.setStyle(TableStyle([
        ("BACKGROUND",    (0,0),(-1,-1), CARD_BG),
        ("LINEBELOW",     (0,0),(-1,-1), 2, colour),
        ("TOPPADDING",    (0,0),(-1,-1), 8),
        ("BOTTOMPADDING", (0,0),(-1,-1), 8),
        ("LEFTPADDING",   (0,0),(-1,-1), 12),
        ("VALIGN",        (0,0),(-1,-1), "MIDDLE"),
    ]))
    return t

# ── Helper: component card ────────────────────────────────────────────────────
def component_card(name, category, description, grid_data, result_name,
                   ingredients, uses, note="", category_colour=ACCENT):

    story = []

    # ---- Header row ----
    hdr = Table([[
        Paragraph(f"<font color='#e94560' size=14><b>{name}</b></font>",
                  ParagraphStyle("cn", fontSize=14, fontName="Helvetica-Bold",
                                 textColor=ACCENT)),
        tag_table(category, bg=category_colour)
    ]], colWidths=[W*0.55, 100])
    hdr.setStyle(TableStyle([
        ("BACKGROUND",    (0,0),(-1,-1), CARD_BG),
        ("TOPPADDING",    (0,0),(-1,-1), 8),
        ("BOTTOMPADDING", (0,0),(-1,-1), 6),
        ("LEFTPADDING",   (0,0),(0,-1), 12),
        ("VALIGN",        (0,0),(-1,-1), "MIDDLE"),
        ("LINEABOVE",     (0,0),(-1,-1), 1.5, CARD_BORDER),
    ]))
    story.append(hdr)

    # ---- Body: description + grid side by side ----
    desc_col = [
        Paragraph(description, ST["body"]),
        Spacer(1, 6),
        Paragraph("<font color='#f5a623'><b>Ingredients:</b></font>", ST["h3"]),
    ]
    for ing in ingredients:
        desc_col.append(Paragraph(f"• {ing}", ST["ingredient"]))

    desc_col.append(Spacer(1, 6))
    desc_col.append(Paragraph("<font color='#4caf50'><b>Used In / For:</b></font>", ST["h3"]))
    for u in uses:
        desc_col.append(Paragraph(f"→ {u}", ST["uses_item"]))

    if note:
        desc_col.append(Spacer(1, 4))
        desc_col.append(Paragraph(f"<i>💡 {note}</i>", ST["small"]))

    grid_label = Paragraph("<font color='#8888aa' size=8><b>Crafting Recipe</b></font>",
                           ParagraphStyle("gl", fontSize=8, textColor=MUTED,
                                          fontName="Helvetica-Bold", alignment=TA_CENTER))
    grid_flowable = CraftingGrid(grid_data, result_name, result_name)

    grid_col = Table(
        [[grid_label], [grid_flowable]],
        colWidths=[170]
    )
    grid_col.setStyle(TableStyle([
        ("BACKGROUND",    (0,0),(-1,-1), colors.HexColor("#0a0a20")),
        ("TOPPADDING",    (0,0),(-1,-1), 6),
        ("BOTTOMPADDING", (0,0),(-1,-1), 6),
        ("ALIGN",         (0,0),(-1,-1), "CENTER"),
    ]))

    body = Table(
        [[Table([[c] for c in desc_col], colWidths=[W*0.52]), grid_col]],
        colWidths=[W*0.52, 175]
    )
    body.setStyle(TableStyle([
        ("BACKGROUND",    (0,0),(0,-1), colors.HexColor("#0d1b3e")),
        ("BACKGROUND",    (1,0),(1,-1), colors.HexColor("#0a0a20")),
        ("VALIGN",        (0,0),(-1,-1), "TOP"),
        ("TOPPADDING",    (0,0),(-1,-1), 8),
        ("BOTTOMPADDING", (0,0),(-1,-1), 10),
        ("LEFTPADDING",   (0,0),(0,-1), 10),
        ("RIGHTPADDING",  (0,0),(0,-1), 8),
        ("LINEBELOW",     (0,0),(-1,-1), 1, colors.HexColor("#1a1a3e")),
    ]))

    story.append(body)
    story.append(Spacer(1, 8))

    return KeepTogether(story)

# ── Component data ────────────────────────────────────────────────────────────
# Grid notation: "" = empty, otherwise short item name

VANILLA_COMPONENTS = [
    dict(
        name="Piston",
        category="Vanilla",
        description="A mechanical block that pushes up to 12 blocks when activated by a Redstone signal. One of the most fundamental building blocks for automated machines, doors, and sorters.",
        grid_data=["Plank","Plank","Plank","Cobble","Iron","Cobble","Cobble","Redstone","Cobble"],
        result_name="Piston",
        ingredients=["3x Wood Planks (any type)", "4x Cobblestone", "1x Iron Ingot", "1x Redstone Dust"],
        uses=["Automated doors & gates", "Item sorters & filters", "Piston engines", "Moving platforms", "Flying machines (with slime/honey)"],
        note="Pistons cannot push obsidian, bedrock, or extended pistons."
    ),
    dict(
        name="Sticky Piston",
        category="Vanilla",
        description="An upgraded piston that both pushes AND pulls the block in front of it. Essential for retractable mechanisms and two-directional contraptions.",
        grid_data=["Slime","","","Piston","","","","",""],
        result_name="Sticky Piston",
        ingredients=["1x Piston", "1x Slimeball"],
        uses=["Retractable bridges", "Hidden doors (2x2, 3x3)", "Piston bolt machines", "Junk filters", "Compact storage systems"],
        note="Crafted in a 2-slot pattern: Slimeball on top, Piston below."
    ),
    dict(
        name="Hopper",
        category="Vanilla",
        description="Collects items from above and transfers them into connected containers below or to the side. The backbone of every automated storage and sorting system.",
        grid_data=["Iron","","Iron","Iron","Chest","Iron","","Iron",""],
        result_name="Hopper",
        ingredients=["5x Iron Ingot", "1x Chest"],
        uses=["Auto-smelters & furnace arrays", "Item sorters", "XP farms", "Auto-crafters (1.21+)", "Item collection from mobs"],
        note="Can be locked with a Redstone signal to pause item transfer."
    ),
    dict(
        name="Dropper",
        category="Vanilla",
        description="Ejects one random item from its inventory per Redstone pulse, or transfers it into an adjacent container. Used in item transport lines and dispensing machines.",
        grid_data=["Cobble","Cobble","Cobble","Cobble","","Cobble","Cobble","Redstone","Cobble"],
        result_name="Dropper",
        ingredients=["7x Cobblestone", "1x Redstone Dust"],
        uses=["Item elevators (dropper pipes)", "Vending machines", "Randomisers", "Auto-crafting pipelines", "Item duplication (water streams)"],
        note="Unlike Dispensers, Droppers always drop items as entities or into containers — never activating them."
    ),
    dict(
        name="Dispenser",
        category="Vanilla",
        description="Fires or activates the item it contains when given a Redstone signal. Can shoot arrows, spawn fire, fill buckets, and much more depending on its contents.",
        grid_data=["Cobble","Cobble","Cobble","Cobble","Bow","Cobble","Cobble","Redstone","Cobble"],
        result_name="Dispenser",
        ingredients=["7x Cobblestone", "1x Bow", "1x Redstone Dust"],
        uses=["Automatic arrow turrets", "Potion throwers", "Fire/TNT cannons", "Shearing sheep farms", "Lava/water source placement"],
        note="The Bow ingredient is consumed; any Bow (even damaged) works."
    ),
    dict(
        name="Observer",
        category="Vanilla",
        description="Detects block state changes on the face it observes and emits a 1-tick Redstone pulse. A highly sensitive sensor for building reactive and self-triggering machines.",
        grid_data=["Cobble","Cobble","Cobble","Redstone","Redstone","Quartz","Cobble","Cobble","Cobble"],
        result_name="Observer",
        ingredients=["6x Cobblestone", "2x Redstone Dust", "1x Nether Quartz"],
        uses=["Zero-tick farms (patched)", "Crop/tree farm triggers", "Clock circuits", "Piston doors (block-update driven)", "Mining machine triggers"],
        note="The observer face looks INTO the block it's placed against — be careful with orientation."
    ),
    dict(
        name="Redstone Comparator",
        category="Vanilla",
        description="Measures or compares container fullness and signal strength. Indispensable for advanced logic gates, item counters, and signal subtraction circuits.",
        grid_data=["","Torch","","Torch","Quartz","Torch","Stone","Stone","Stone"],
        result_name="Comparator",
        ingredients=["3x Redstone Torch", "1x Nether Quartz", "3x Stone"],
        uses=["Container fullness sensors", "Combination locks", "Item counters", "Subtraction circuits", "Jukebox note detection"],
        note="Two modes: compare (default) and subtract — right-click to toggle."
    ),
    dict(
        name="Redstone Repeater",
        category="Vanilla",
        description="Amplifies a Redstone signal back to full strength, introduces a delay of 1–4 ticks, and enforces signal direction. Critical for long circuits and timing control.",
        grid_data=["","","","Torch","RedstoneX","Torch","Stone","Stone","Stone"],
        result_name="Repeater",
        ingredients=["2x Redstone Torch", "1x Redstone Dust", "3x Stone"],
        uses=["Signal delay lines", "Signal amplification over distance", "Clock circuits", "Locking mechanisms", "Direction-only signal paths"],
        note="Right-click to cycle delay: 1, 2, 3, or 4 ticks. Can be locked by a signal from the side."
    ),
]

CREATE_COMPONENTS = [
    dict(
        name="Andesite Alloy",
        category="Create Mod",
        description="The most fundamental crafting material in the Create mod. A blend of Andesite stone and Iron that forms the base of nearly every Create component. Think of it as Create's version of Iron.",
        grid_data=["Andesite","Iron","","Iron","Andesite","","","",""],
        result_name="Andesite Alloy",
        ingredients=["1x Polished Andesite", "2x Iron Nugget (or Zinc Nugget)"],
        uses=["Cogwheels", "Mechanical Shafts", "Mechanical Pistons", "Andesite Casing", "Almost every Create component"],
        note="Crafted in a 2x2 diagonal pattern. Zinc Nuggets are interchangeable with Iron Nuggets."
    ),
    dict(
        name="Cogwheel",
        category="Create Mod",
        description="Transfers and redirects rotational power between machines. Cogwheels mesh with adjacent cogwheels to relay Rotational Force (RPM & Stress) across your network.",
        grid_data=["","Plank","","Plank","Alloy","Plank","","Plank",""],
        result_name="Cogwheel",
        ingredients=["1x Andesite Alloy", "4x Wood Planks (any)"],
        uses=["Connecting power sources to machines", "Changing rotation direction", "Gear ratios (with Large Cogwheel)", "Mechanical Crafters", "All rotational machinery"],
        note="A small and large cogwheel together change the RPM ratio (2:1). Cogs must be aligned properly to mesh."
    ),
    dict(
        name="Mechanical Shaft",
        category="Create Mod",
        description="Transmits rotational power in a straight line over long distances. Acts as the 'wire' of the Create power network, connecting distant machines and power sources.",
        grid_data=["","Alloy","","","Alloy","","","Alloy",""],
        result_name="Shaft",
        ingredients=["2x Andesite Alloy"],
        uses=["Long-distance power transmission", "Connecting Water Wheels to machines", "Encased Shaft lines", "Rotational speed testing", "Windmill to machine connection"],
        note="Enclosed in Andesite or Brass Casing to prevent exposed spinning shafts."
    ),
    dict(
        name="Mechanical Piston",
        category="Create Mod",
        description="A Create-powered piston that extends and retracts based on rotational input direction. Unlike vanilla pistons, these can move large structures when equipped with Piston Extension Poles.",
        grid_data=["","Iron","","Alloy","Piston","Alloy","","Iron",""],
        result_name="Mech. Piston",
        ingredients=["2x Iron Ingot", "2x Andesite Alloy", "1x Vanilla Piston"],
        uses=["Moving large structures", "Automated mining rigs", "Elevators", "Crushing platforms", "Animated builds"],
        note="Extend range using Piston Extension Poles. Needs a chassis to move multi-block structures."
    ),
    dict(
        name="Water Wheel",
        category="Create Mod",
        description="Generates Rotational Force (stress capacity & RPM) from flowing water. The most accessible early-game power source in Create — just place it next to a water stream.",
        grid_data=["","Plank","","Plank","Shaft","Plank","","Plank",""],
        result_name="Water Wheel",
        ingredients=["1x Mechanical Shaft", "8x Wood Planks (any)"],
        uses=["Early-game power generation", "Driving Millstones", "Driving Mechanical Presses", "Powering fans & mixers", "Combined arrays for higher stress capacity"],
        note="Produces 16 RPM and 256 Stress Units. Multiple wheels on one shaft add capacity, not RPM."
    ),
    dict(
        name="Millstone",
        category="Create Mod",
        description="Grinds items into powder or other by-products using rotational power. An essential early processing machine for making flour, gravel, and other crushed materials.",
        grid_data=["","Stone","","Alloy","Stone","Alloy","","Stone",""],
        result_name="Millstone",
        ingredients=["3x Stone", "2x Andesite Alloy"],
        uses=["Crushing ores into nuggets (early)", "Making flour for bread", "Producing gravel/sand/flint", "Pulverising netherrack", "Bulk material processing"],
        note="Items can be fed via Funnels from above or by dropping them on top."
    ),
    dict(
        name="Mechanical Press",
        category="Create Mod",
        description="Presses items into sheets and compacts materials when activated by a vertically-moving Mechanical Piston. Essential for mid-game metal sheet production.",
        grid_data=["","Iron","","Alloy","Iron","Alloy","","Alloy",""],
        result_name="Mech. Press",
        ingredients=["2x Iron Ingot", "3x Andesite Alloy"],
        uses=["Making Iron/Copper/Brass Sheets", "Compacting Rose Quartz", "Creating Dough from Wheat", "Automated sheet production lines", "Processing packed materials"],
        note="Must be directly above a Basin or Belt to function. Needs a Mech. Piston pushing it down."
    ),
    dict(
        name="Encased Fan",
        category="Create Mod",
        description="Converts rotational power into an air stream that can smelt, wash, or haul items. One of the most versatile machines in Create — essential for bulk item processing.",
        grid_data=["Iron","Iron","Iron","Shaft","Casing","Iron","Iron","Iron","Iron"],
        result_name="Enc. Fan",
        ingredients=["1x Mechanical Shaft", "1x Andesite Casing", "7x Iron Nugget"],
        uses=["Bulk smelting (fan + lava/fire)", "Washing ores (fan + water)", "Item launchers/transportation", "Cobblestone generator combos", "Ore doubling setups"],
        note="Air direction depends on rotation direction. Place lava in front for smelting, water for washing."
    ),
    dict(
        name="Brass Casing",
        category="Create Mod",
        description="A mid-game decorative and functional casing used in advanced Create components. Made from Brass Ingots, which require mixing Copper and Zinc in a Mechanical Mixer.",
        grid_data=["Brass","Brass","Brass","Brass","Shaft","Brass","Brass","Brass","Brass"],
        result_name="Brass Casing",
        ingredients=["8x Brass Ingot", "1x Mechanical Shaft"],
        uses=["Encased Fans (brass variant)", "Smart pipes & Funnels", "Deployers", "Mechanical Arms", "Sequenced Gearshift"],
        note="Brass requires Copper + Zinc in a 1:1 ratio, processed in a Mechanical Mixer with heat."
    ),
    dict(
        name="Mechanical Mixer",
        category="Create Mod",
        description="Combines multiple ingredients in a Basin using rotational power. Required for making Brass, Chocolate, Dough, and many other mixed recipes. Needs heat for some recipes.",
        grid_data=["","Alloy","","Alloy","Whisk","Alloy","","Alloy",""],
        result_name="Mixer",
        ingredients=["3x Andesite Alloy", "1x Whisk (made from Iron Ingots)"],
        uses=["Making Brass Ingots (Cu + Zn)", "Mixing food ingredients", "Crafting advanced materials", "Bulk fluid mixing", "Automation of multi-ingredient recipes"],
        note="A Blaze Burner underneath the Basin provides heat for temperature-sensitive recipes."
    ),
]

UNIVERSAL_COMPONENTS = [
    dict(
        name="Iron Block",
        category="Universal",
        description="A compact storage block and structural material. Used both as decoration and as a crafting ingredient for constructs requiring large amounts of iron.",
        grid_data=["Iron","Iron","Iron","Iron","Iron","Iron","Iron","Iron","Iron"],
        result_name="Iron Block",
        ingredients=["9x Iron Ingot"],
        uses=["Iron Golem construction (+ pumpkin)", "Beacon pyramid base", "Compact iron storage", "Create: Mechanical Arm crafting", "Blast Furnace ingredient"],
        note="Can be reversed — craft back into 9 Iron Ingots in a crafting table."
    ),
    dict(
        name="Blast Furnace",
        category="Universal",
        description="Smelts iron and gold ores, tools, and armour at double the speed of a regular furnace. A mandatory upgrade for any large-scale metal production line.",
        grid_data=["Iron","Iron","Iron","Furnace","Furnace","Furnace","Stone","Stone","Stone"],
        result_name="Blast Furnace",
        ingredients=["3x Iron Ingot", "3x Smooth Stone", "3x Furnace"],
        uses=["Fast iron/gold ore smelting", "Smelting armour & tools into nuggets", "Hopper auto-smelter arrays", "Create Mod: Blaze Burner fuel chain", "Automated ore processing lines"],
        note="Does NOT smelt food or other non-metal items — use a regular Furnace for those."
    ),
    dict(
        name="Crafting Table",
        category="Universal",
        description="The foundation of all crafting in Minecraft. Every machine, tool, and component ultimately requires a Crafting Table to produce. Also deployable as an auto-crafter with hoppers.",
        grid_data=["Plank","Plank","","Plank","Plank","","","",""],
        result_name="Craft Table",
        ingredients=["4x Wood Planks (any type)"],
        uses=["Crafting all items & components", "Auto-crafter ingredient (1.21+)", "Create: Mechanical Crafter component", "Villager workstation (Fletcher)", "Foundation of every machine build"],
        note="Crafted in a 2x2 pattern in your personal inventory grid — no table needed."
    ),
    dict(
        name="Chest",
        category="Universal",
        description="27-slot storage container and fundamental input/output node for nearly every machine and automation system. Double Chests provide 54 slots.",
        grid_data=["Plank","Plank","Plank","Plank","","Plank","Plank","Plank","Plank"],
        result_name="Chest",
        ingredients=["8x Wood Planks (any type)"],
        uses=["Hopper output buffers", "Sorting system endpoints", "Dropper/Dispenser inventory", "Create: Vault alternative", "Item collection & organisation"],
        note="Place two chests side-by-side for a 54-slot Double Chest."
    ),
]

# ── Build PDF ─────────────────────────────────────────────────────────────────
def build_pdf(path):
    doc = SimpleDocTemplate(
        path, pagesize=A4,
        leftMargin=20*mm, rightMargin=20*mm,
        topMargin=18*mm, bottomMargin=18*mm,
        title="Machine Components Guide",
        author="Claude AI",
    )

    story = []

    # --- Cover page ---
    story.append(Spacer(1, H - 40))  # placeholder; cover drawn in onFirstPage
    story.append(PageBreak())

    # ── SECTION 1: Vanilla ────────────────────────────────────────────────────
    story.append(section_header("⚙  SECTION 1: VANILLA REDSTONE COMPONENTS",
                                "Core blocks for Redstone automation", ACCENT))
    story.append(Spacer(1, 6))
    story.append(Paragraph(
        "These components are available in every Minecraft world with no mods required. "
        "They form the foundation of all automated systems — from simple doors to complex "
        "sorting machines and mob farms.",
        ST["section_intro"]
    ))
    story.append(HRFlowable(width="100%", thickness=1, color=ACCENT, spaceAfter=8))

    for comp in VANILLA_COMPONENTS:
        story.append(component_card(**comp, category_colour=ACCENT))

    story.append(PageBreak())

    # ── SECTION 2: Create Mod ─────────────────────────────────────────────────
    story.append(section_header("🔩  SECTION 2: CREATE MOD COMPONENTS",
                                "Rotational power & mechanical automation", GOLD))
    story.append(Spacer(1, 6))
    story.append(Paragraph(
        "The Create Mod adds mechanical power through rotation, stress, and RPM. "
        "Components must be connected in a valid rotational network. Power sources "
        "(Water Wheels, Windmills, Engines) feed into machines via Shafts and Cogwheels.",
        ST["section_intro"]
    ))
    story.append(HRFlowable(width="100%", thickness=1, color=GOLD, spaceAfter=8))

    for comp in CREATE_COMPONENTS:
        story.append(component_card(**comp, category_colour=GOLD))

    story.append(PageBreak())

    # ── SECTION 3: Universal ──────────────────────────────────────────────────
    story.append(section_header("🔧  SECTION 3: UNIVERSAL MACHINE PARTS",
                                "Essential blocks used across all systems", TEAL))
    story.append(Spacer(1, 6))
    story.append(Paragraph(
        "These components appear in virtually every build — from basic survival to "
        "end-game megastructures. Mastering their use and automation is essential for "
        "any efficient machine setup.",
        ST["section_intro"]
    ))
    story.append(HRFlowable(width="100%", thickness=1, color=TEAL, spaceAfter=8))

    for comp in UNIVERSAL_COMPONENTS:
        story.append(component_card(**comp, category_colour=TEAL))

    story.append(PageBreak())

    # ── Quick Reference Table ─────────────────────────────────────────────────
    story.append(section_header("📋  QUICK REFERENCE",
                                "All components at a glance", BLUE))
    story.append(Spacer(1, 8))

    ref_data = [
        [Paragraph("<b>Component</b>", ST["tag"]),
         Paragraph("<b>Category</b>", ST["tag"]),
         Paragraph("<b>Key Ingredient</b>", ST["tag"]),
         Paragraph("<b>Primary Use</b>", ST["tag"])],
    ]
    ref_style_light = ParagraphStyle("rl", fontSize=8.5, textColor=LIGHT_TEXT,
                                     fontName="Helvetica", leading=12)
    ref_style_muted = ParagraphStyle("rm", fontSize=8.5, textColor=MUTED,
                                     fontName="Helvetica", leading=12)

    rows = [
        ("Piston",            "Vanilla",   "Iron Ingot",       "Push blocks / machines"),
        ("Sticky Piston",     "Vanilla",   "Slimeball",        "Pull & push blocks"),
        ("Hopper",            "Vanilla",   "Iron Ingot",       "Item transport & sorting"),
        ("Dropper",           "Vanilla",   "Redstone Dust",    "Item transfer / elevators"),
        ("Dispenser",         "Vanilla",   "Bow",              "Projectile / tool activation"),
        ("Observer",          "Vanilla",   "Nether Quartz",    "Block-change detection"),
        ("Comparator",        "Vanilla",   "Nether Quartz",    "Signal logic / sensors"),
        ("Repeater",          "Vanilla",   "Redstone Torch",   "Delay / amplification"),
        ("Andesite Alloy",    "Create",    "Iron Nugget",      "Base Create material"),
        ("Cogwheel",          "Create",    "Andesite Alloy",   "Rotational power transfer"),
        ("Mechanical Shaft",  "Create",    "Andesite Alloy",   "Long-distance power line"),
        ("Mech. Piston",      "Create",    "Vanilla Piston",   "Moving structures"),
        ("Water Wheel",       "Create",    "Wood Planks",      "Early power generation"),
        ("Millstone",         "Create",    "Stone",            "Grinding / crushing"),
        ("Mech. Press",       "Create",    "Iron Ingot",       "Making metal sheets"),
        ("Encased Fan",       "Create",    "Andesite Casing",  "Bulk smelting / washing"),
        ("Brass Casing",      "Create",    "Brass Ingot",      "Advanced components"),
        ("Mech. Mixer",       "Create",    "Whisk",            "Mixing / Brass production"),
        ("Iron Block",        "Universal", "Iron Ingot (x9)",  "Golem / beacon / storage"),
        ("Blast Furnace",     "Universal", "Furnace (x3)",     "Fast metal smelting"),
        ("Crafting Table",    "Universal", "Wood Planks",      "All crafting"),
        ("Chest",             "Universal", "Wood Planks",      "Item storage / buffers"),
    ]
    for i, (comp, cat, key, use) in enumerate(rows):
        col = CARD_BG if i % 2 == 0 else colors.HexColor("#081428")
        ref_data.append([
            Paragraph(comp, ref_style_light),
            Paragraph(cat,  ref_style_muted),
            Paragraph(key,  ref_style_muted),
            Paragraph(use,  ref_style_light),
        ])

    ref_table = Table(ref_data, colWidths=[110, 70, 105, 165])
    ref_ts = TableStyle([
        ("BACKGROUND",    (0,0),(-1,0), ACCENT),
        ("TEXTCOLOR",     (0,0),(-1,0), DARK_BG),
        ("FONTNAME",      (0,0),(-1,0), "Helvetica-Bold"),
        ("FONTSIZE",      (0,0),(-1,0), 9),
        ("ALIGN",         (0,0),(-1,-1), "LEFT"),
        ("TOPPADDING",    (0,0),(-1,-1), 5),
        ("BOTTOMPADDING", (0,0),(-1,-1), 5),
        ("LEFTPADDING",   (0,0),(-1,-1), 7),
        ("GRID",          (0,0),(-1,-1), 0.5, GRID_BORDER),
    ])
    for i in range(1, len(ref_data)):
        bg = CARD_BG if i % 2 == 1 else colors.HexColor("#081428")
        ref_ts.add("BACKGROUND", (0,i), (-1,i), bg)
        ref_ts.add("TEXTCOLOR",  (0,i), (-1,i), LIGHT_TEXT)
    ref_table.setStyle(ref_ts)
    story.append(ref_table)

    # ── Build ──────────────────────────────────────────────────────────────────
    frame_count = [0]
    def onPage(c, doc):
        if doc.page == 1:
            draw_cover(c, doc)
        else:
            draw_background(c, doc)

    doc.build(story, onFirstPage=onPage, onLaterPages=onPage)
    print("Done!")

build_pdf("/mnt/user-data/outputs/Machine_Components_Guide.pdf")