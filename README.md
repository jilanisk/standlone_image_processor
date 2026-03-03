 <img width="1024" height="309" alt="github banner" src="https://github.com/user-attachments/assets/5ea24d2c-ce77-4fda-8907-1f659031536b" />


📦 Standalone Image Processor
 
A lightweight standalone PHP utility that processes an Excel file containing image paths and skewId, then automatically:

📁 Creates skewId-based folders

🖼 Copies images into respective folders

🔢 Renames them sequentially (skewId_01.jpg, skewId_02.jpg)

No framework required. Simple. Fast. Production-ready.

✨ Features

✅ Excel (.xlsx) upload support

✅ Automatic folder creation per skewId

✅ Sequential image renaming

✅ Clean directory structure

✅ Standalone PHP (no Laravel/CI required)

✅ Composer-based dependency handling

📂 Project Structure
 Standalone_image_processor/
 
│
 
├── uploads/              # Uploaded Excel files

├── uploadFolder/         # Generated skewId folders

├── vendor/               # Extracted vendor files

├── vendor.zip            # Vendor archive (must extract)

├── index.php

└── README.md

🛠 Requirements

PHP 8.0+

Extensions:

fileinfo

zip

mbstring

Web server (Apache / Nginx / XAMPP / PHP built-in server)

⚙️ Installation

1️⃣ Clone Repository

git clone https://github.com/your-username/Standalone_image_processor.git

cd Standalone_image_processor

2️⃣ Extract Vendor Files


After cloning, extract:

vendor.zip

This will create:

/vendor

⚠️ Mandatory step

The project depends on: PhpOffice,PhpSpreadsheet

3️⃣ Set Folder Permissions

Make sure these folders are writable:

uploads/

uploadFolder/

Linux/Mac:

chmod -R 775 uploads uploadFolder

4️⃣ Run Application

Option A – PHP Built-in Server

php -S localhost:8000

Open in browser:

http://localhost:8000

Option B – XAMPP / Apache

Place project inside:

htdocs/

Access via:

http://localhost/Standalone_image_processor
📊 Excel Format

Your Excel file must contain exactly 2 columns:
  
   imagePath	skewId

   
   /images/a.jpg	SKU1001
   /images/b.jpg	SKU1001
   /images/c.jpg	SKU2002
   ⚠️ Important

Column order must match.

Image paths must be accessible to the server.

Only .xlsx format supported.

📁 Output Example

If Excel contains:

SKU1001 → 2 images
SKU2002 → 1 image

Generated structure:

uploadFolder/
│
├── SKU1001/
│   ├── SKU1001_01.jpg
│   └── SKU1001_02.jpg
│
└── SKU2002/
    └── SKU2002_01.jpg
🔄 Processing Flow

Upload Excel file

Parse using PhpSpreadsheet

Group records by skewId

Create folder for each skewId

Copy & rename images sequentially

Store inside uploadFolder/

❗ Error Handling
Scenario	Behavior
Image not found	Skipped
Invalid Excel	Upload rejected
Missing vendor	Script fails
🔐 Security Notes

Validate file type before processing

Do not allow arbitrary external URLs unless needed

Restrict folder permissions in production

🚀 Production Deployment Tips

Disable display errors

Enable error logging

Use proper directory permissions

Consider adding file size limits

Add progress bar for large datasets

🧪 Future Improvements (Optional)

⏳ Progress indicator

📊 Status summary (copied/missing)

📥 Export result report to Excel

🌙 Dark mode UI

🧩 Bootstrap/Tailwind UI upgrade

🐳 Docker support

📜 License

MIT License – Free to use and modify.

👨‍💻 Author
Jilani Shaik


**Applcation Screenshots**

<img width="1919" height="904" alt="Screenshot 2026-03-03 130523" src="https://github.com/user-attachments/assets/e8d322d9-dfd2-471b-9e76-44adbcb4f38f" />

<img width="1919" height="722" alt="Screenshot 2026-03-03 130549" src="https://github.com/user-attachments/assets/9c570b85-5b4e-40b7-a87c-ffa1e38c3246" />

<img width="1719" height="864" alt="Screenshot 2026-03-03 131032" src="https://github.com/user-attachments/assets/ebd00fea-5e9a-4f39-abc0-5ca6959416b0" />



Standalone utility built for bulk image organization automation.
