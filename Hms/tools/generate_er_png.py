from PIL import Image, ImageDraw, ImageFont

W, H = 1400, 900
bg = (255,255,255)
img = Image.new('RGB', (W, H), color=bg)
d = ImageDraw.Draw(img)

try:
    font_b = ImageFont.truetype("DejaVuSans-Bold.ttf", 16)
    font = ImageFont.truetype("DejaVuSans.ttf", 14)
except Exception:
    font_b = ImageFont.load_default()
    font = ImageFont.load_default()

# Helper to draw a table box with title and columns
def draw_table(x, y, title, columns, w=300, h=None):
    pad = 8
    line_h = 20
    if h is None:
        h = 30 + line_h * len(columns)
    d.rectangle([x, y, x+w, y+h], outline=(0,0,0), width=2)
    # title background
    d.rectangle([x, y, x+w, y+30], fill=(230,230,250))
    d.text((x+pad, y+6), title, fill=(0,0,0), font=font_b)
    # columns
    yy = y+36
    for col in columns:
        d.text((x+pad, yy), col, fill=(0,0,0), font=font)
        yy += line_h
    return (x, y, x+w, y+h)

# Define tables and positions
tables = {}

tables['admin'] = draw_table(550, 20, 'admin', ['id (PK)', 'username', 'password'], w=300)

tables['hostel'] = draw_table(50, 110, 'hostel', ['Hostel_id (PK)', 'Hostel_name', 'location', 'Total_rooms'])
tables['room'] = draw_table(50, 320, 'room', ['Room_id (PK)', 'Room_name', 'Room_type', 'capacity', 'status', 'Hostel_id (FK)'])
tables['warden'] = draw_table(50, 560, 'warden', ['Warden_id (PK)', 'Name', 'username', 'password', 'Phone', 'Email', 'Hostel_id (FK)'])

tables['student'] = draw_table(525, 200, 'student', ['Student_id (PK)', 'name', 'username', 'password', 'gender', 'course', 'semester', 'phone', 'email', 'Room_id (FK)'])

tables['student_password_reset'] = draw_table(525, 460, 'student_password_reset', ['Reset_id (PK)', 'Student_id (FK)', 'otp_hash', 'expires_at', 'used_at', 'created_at'])
tables['leave_request'] = draw_table(525, 620, 'leave_request', ['Leave_id (PK)', 'Student_id (FK)', 'Start_date', 'End_date', 'Reason', 'Status'])

tables['payment'] = draw_table(1000, 110, 'payment', ['Payment_id (PK)', 'Payment_Date', 'amount', 'status', 'Student_id (FK)'])
tables['attendance'] = draw_table(1000, 320, 'attendance', ['Attendance_id (PK)', 'Date', 'In_time', 'Out_time', 'Student_id (FK)'])
tables['complaint'] = draw_table(1000, 520, 'complaint', ['Complaint_id (PK)', 'Student_id (FK)', 'Description', 'Date', 'Status'])

# Draw relationships as lines/arrows
def center(rect):
    x1,y1,x2,y2 = rect
    return ((x1+x2)//2, (y1+y2)//2)

# hostel (Hostel_id) -> room.Hostel_id
h_mid = center(tables['hostel'])
room_mid = center(tables['room'])
d.line([ (h_mid[0]+120, h_mid[1]+10), (room_mid[0]-50, room_mid[1]-80) ], fill=(0,0,0), width=2)
d.text(((h_mid[0]+120+room_mid[0]-50)/2-40, (h_mid[1]+10+room_mid[1]-80)/2-10), '1..*', font=font)

# room -> student (Room_id)
room_mid = center(tables['room'])
student_mid = center(tables['student'])
d.line([ (room_mid[0]+130, room_mid[1]), (student_mid[0]-150, student_mid[1]-50) ], fill=(0,0,0), width=2)
d.text(((room_mid[0]+130+student_mid[0]-150)/2-40, (room_mid[1]+student_mid[1]-50)/2-10), '1..*', font=font)

# hostel -> warden
h_mid = center(tables['hostel'])
w_mid = center(tables['warden'])
d.line([ (h_mid[0]+120, h_mid[1]+10), (w_mid[0]-50, w_mid[1]-80) ], fill=(0,0,0), width=2)
d.text(((h_mid[0]+120+w_mid[0]-50)/2-40, (h_mid[1]+10+w_mid[1]-80)/2-10), '1..*', font=font)

# student -> payment, attendance, complaint, leave_request, student_password_reset
s_mid = center(tables['student'])
for t in ['payment', 'attendance', 'complaint', 'leave_request', 'student_password_reset']:
    t_mid = center(tables[t])
    d.line([ (s_mid[0]+150, s_mid[1]), (t_mid[0]-130, t_mid[1]) ], fill=(0,0,0), width=2)
    d.text(((s_mid[0]+150+t_mid[0]-130)/2-20, (s_mid[1]+t_mid[1])/2-10), '1..*', font=font)

# student -> student_password_reset (already drawn)

# admin standalone label
d.text((560, 60), '(admin: standalone)', fill=(80,80,80), font=font)

# Save
img.save('/opt/lampp/htdocs/Hms/er_diagram.png')
print('Saved /opt/lampp/htdocs/Hms/er_diagram.png')
