# Moodle LRS Plugin

A Moodle local plugin that sends xAPI statements to an external Learning Record Store (LRS). Designed to integrate seamlessly with the Saudi National eLearning Center (NELC) and compatible with any xAPI-compliant LRS.

## 📋 Table of Contents

- [Features](#-features)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Database Schema](#-database-schema)
- [xAPI Events](#-xapi-events)
- [Support](#-support)
- [License](#-license)

## ✨ Features

- **xAPI Statement Generation** - Automatically generates and sends xAPI statements for key learning activities
- **NELC Integration** - Full compatibility with Saudi National eLearning Center xAPI standards
- **Course Duration Tracking** - Extended course and lesson metadata including duration fields
- **Multi-language Support** - Platform names in both English and Arabic
- **Activity Tracking** - Comprehensive tracking of:
  - User activity initialization
  - Course enrollment (registration)
  - Video watching and virtual class attendance
  - Lesson, module, and course completion
  - Quiz attempts with scoring
  - Learning progress tracking
  - Course ratings and certificate issuance

## 📦 Requirements

| Component | Minimum Version |
|-----------|----------------|
| Moodle | 4.0+ (2022041900) |
| PHP | As per Moodle requirements |
| Required Plugin | `tool_courserating` (any version) |
| LRS | Any xAPI-compliant Learning Record Store |

## 🚀 Installation

### Step 1: Upload Plugin Files

Upload the plugin folder to your Moodle installation directory:

```bash
moodle/public/local/
```

> **⚠️ IMPORTANT:** The plugin folder **must** be named exactly `moodle_lrs_plugin` for Moodle to detect it correctly. If you downloaded a zip file (e.g., `moodle-lrs-plugin-master.zip`), extract it and rename the folder to `moodle_lrs_plugin` before uploading.

Final folder path after uploading:

```bash
moodle/public/local/moodle_lrs_plugin
```

### Step 2: Install via Moodle Interface

1. Log in to your Moodle site as a **Site Administrator**
2. Navigate to **Site administration** → **Notifications**
3. Moodle will automatically detect the new plugin
4. Click **Upgrade Moodle database now**

### Step 3: Install Dependencies

During installation, Moodle will check for required dependencies. If the `tool_courserating` plugin is missing:

1. Install the required plugin from the Moodle plugins directory
2. Return to the notifications page
3. Complete the Moodle LRS Plugin installation

### Step 4: Complete Setup

Click **Continue** to finish the installation process.

## ⚙️ Configuration

After successful installation, configure the plugin settings:

1. Navigate to: **Site administration** → **Plugins** → **Local plugins** → **Moodle LRS Plugin**
2. Configure the following parameters:

| Setting | Description | Example |
|---------|-------------|---------|
| **LRS Endpoint** | URL of your Learning Record Store | `https://lrs.example.com/xapi/` |
| **LRS Username** | Authentication username for LRS | `moodle_user` |
| **LRS Password** | Authentication password for LRS | `secure_password` |
| **Platform Name (English)** | Platform display name in English | `National Learning Platform` |
| **Platform Name (Arabic)** | Platform display name in Arabic | `المنصة الوطنية للتعلم` |

3. Click **Save changes**

## 🗄️ Database Schema

The plugin automatically extends existing Moodle tables with the following fields:

### Course Table (`mdl_course`)

| Field | Type | Default | Description |
|-------|------|---------|-------------|
| `course_duration` | INTEGER(10) | 0 | Course duration in minutes |
| `course_language` | CHAR(10) | 'en-US' | Course language code |
| `is_nelc_enabled` | INTEGER(1) | 1 | NELC integration flag |

### Lesson Table (`mdl_lesson`)

| Field | Type | Default | Description |
|-------|------|---------|-------------|
| `lesson_duration` | INTEGER(10) | 0 | Lesson duration in minutes |

### Resource Table (`mdl_resource`)

| Field | Type | Default | Description |
|-------|------|---------|-------------|
| `lesson_duration` | INTEGER(10) | 0 | Resource duration in minutes |

> **Note:** All fields are created automatically during plugin installation. No manual database modifications are required.

## 📊 xAPI Events

The plugin tracks and generates xAPI statements for the following Moodle events, fully compliant with NELC xAPI standards:

### Supported Verbs & Activities

| Event | Verb | Description | Activity Type |
|-------|------|-------------|---------------|
| **User Login** | `initialized` | Tracks when a learner successfully starts an activity in the LMS | N/A |
| **Course Enrollment** | `registered` | Records when users officially enroll in a course | `course` |
| **Video Watched** | `watched` | Captures video consumption (90% = watched) | `video` |
| **Lesson Completed** | `completed` | Monitors individual lesson completions | `lesson` |
| **Virtual Class Attended** | `attended` | Tracks attendance in live virtual classroom sessions | `virtual-classroom` |
| **Quiz Attempted** | `attempted` | Records quiz attempts with scores and success status | `unit-test` / `assessment` |
| **Module Completed** | `completed` | Captures completion of course modules/units | `module` |
| **Course Progress** | `progressed` | Tracks ongoing learning progress percentage | `course` |
| **Course Completed** | `completed` | Records full course completion | `course` |
| **Course Rated** | `rated` | Captures learner ratings and reviews | `course` |
| **Certificate Earned** | `earned` | Records certificate issuance with verification link | `certificate` |

### Statement Features

- **NELC Compliance**: All statements follow Saudi National eLearning Center xAPI profile guidelines
- **Unique Identification**: Uses National ID/Iqama as actor identifier
- **ISO Standards**: Durations in ISO 8601 format (`PT1H30M00S`), languages in ISO 639-1 (`ar-SA`, `en-US`)
- **Hierarchical Context**: Maintains parent-child relationships between courses, modules, and activities
- **Metadata Rich**: Includes instructor info, platform details, browser data, and custom NELC extensions

All statements comply with xAPI 1.0.3 specification and NELC integration requirements.

## 📞 Support

Need help with installation, integration, or custom development?

### 📱 WhatsApp
- [+20 100 094 4804](https://wa.me/201000944804)
- [+20 106 233 2549](https://wa.me/201062332549)

### 📧 Email
- [info@bzzix.com](mailto:info@bzzix.com)
- [bzzixs@gmail.com](mailto:bzzix@gmail.com)

## 📄 License

This plugin is licensed under the **MIT License**. See the [LICENSE](LICENSE) file for details.

Custom modifications and enterprise support are available upon request.

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

**Developed by:** Mohammed Hassan  
**Copyright:** 2025  
**Version:** 2.0.2