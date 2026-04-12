# Moodle LRS Plugin (NELC Integration)

A comprehensive Moodle local plugin that captures learning events and sends them as xAPI statements to an external Learning Record Store (LRS). This plugin is specifically designed to integrate seamlessly with the **Saudi National eLearning Center (NELC)** standards, and works efficiently with any standard xAPI-compliant LRS.

## 📋 Table of Contents

- [Features](#-features)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Database Schema & Profile Fields](#-database-schema--profile-fields)
- [xAPI Events Tracking](#-xapi-events-tracking)
- [Usage & User Profiling](#-usage--user-profiling)
- [Upgrading](#-upgrading)
- [Troubleshooting](#-troubleshooting)
- [Support](#-support)
- [License](#-license)

## ✨ Features

- **xAPI Statement Generation:** Automatically generates and sends compliant xAPI statements for key learning activities inside Moodle.
- **NELC Compliance:** Perfectly tailored for the Saudi National eLearning Center xAPI guidelines. Uses National ID/Iqama as the actor identifier (`actor.name`).
- **Automated Profile Fields:** Automatically builds required Moodle custom user profile fields (e.g. `national_id`) during installation or upgrade, and forces them to show during signup.
- **Duration Tracking:** Extends Moodle’s course, lesson, and resource metadata with precise duration fields tailored for LRS statistics.
- **Multi-language Support:** Adapts platform and course names in both English and Arabic dynamically based on user and course preferences.
- **Micro-interactions Tracking:** Deep tracking for video watched, quiz attempts (with precise scoring mapping), completion statuses, and course ratings.
- **Real-time Notifications:** Uses `iziToast` to immediately notify the learner of successful data delivery to the National Center upon completing tasks.

## 📦 Requirements

| Component | Required Version |
|-----------|----------------|
| Moodle | 4.0 or higher (Build: `2022041900+`) |
| PHP | Compatible with your Moodle version |
| Required Plugins | `tool_courserating` (Required for course ratings tracking) |
| LRS | Any standard xAPI-compliant Learning Record Store (LRS) |

## 🚀 Installation

### Step 1: Upload Plugin Files
Upload or extract the plugin folder into your Moodle’s `local` directory:
```bash
/moodle_path/local/moodle_lrs_plugin
```
> **⚠️ IMPORTANT:** The folder must exactly be named `moodle_lrs_plugin` (not `moodle-lrs-plugin-master` or anything else) for Moodle to detect it.

### Step 2: Install via Moodle Interface
1. Login to your Moodle as an **Administrator**.
2. Navigate to **Site administration** → **Notifications**.
3. Moodle will detect the plugin. Click **Upgrade Moodle database now**.
4. *(Optional)* If Moodle warns about missing dependencies (like `tool_courserating`), install it first from the Moodle plugins directory, then resume this installation.

### Step 3: Complete Setup
Upon clicking **Continue**, Moodle will execute the plugin's `install.php` which automatically creates the necessary database tables extensions and injects the `national_id` profile field into Moodle.

## ⚙️ Configuration

Configure the connection to your LRS by navigating to:
**Site administration** → **Plugins** → **Local plugins** → **Moodle LRS Plugin**

| Setting | Description | Example |
|---------|-------------|---------|
| **LRS Endpoint** | Full URL of your LRS xAPI endpoint | `https://lrs.example.com/data/xAPI/` |
| **LRS Username** | Basic Auth Username assigned by your LRS | `my_lrs_client` |
| **LRS Password** | Basic Auth Password/Token assigned by your LRS | `my_secure_secret` |
| **Platform Name (English)** | English name of your LMS | `National Learning Platform` |
| **Platform Name (Arabic)** | Arabic name of your LMS | `المنصة الوطنية للتعلم` |

## 🗄️ Database Schema & Profile Fields

### Automatic Database Extensions
The plugin automatically injects fields into existing Moodle tables without modifying core files:
- **`mdl_course`**: Injects `course_duration`, `course_language`, and `is_nelc_enabled` (toggle NELC tracking on a per-course basis).
- **`mdl_lesson`** and **`mdl_resource`**: Injects `lesson_duration` to define the duration in minutes.

### Automatic Profile Fields (National ID)
During installation/upgrade, the plugin creates a Custom User Profile Field named **National ID / رقم الهوية الوطنية**:
- **Shortname:** `national_id` (Programmatically queried by the plugin).
- **Visibility:** Forced to appear on the User Signup page.
- **Requirement:** Marked as *Required* so users cannot bypass it.
The xAPI generator will always look for this `national_id` field and use it primarily as the `actor.name` identifier in the LRS payloads to meet NELC standards.

## 📊 xAPI Events Tracking

The plugin listens to various Moodle events and fires corresponding xAPI verbs:

| Moodle Action | xAPI Verb | Payload / Context Details |
|--------------|-----------|---------------------------|
| **Course Enrollment** | `registered` / `initialized` | Triggers when a user fully registers. Contains learner's National ID, mobile, DoB, and instructor details. |
| **Resource/Lesson Completing**| `completed` | Captures lesson/resource ID, title, and exact defined duration (`PTxxxM`). |
| **Section Completion** | `completed` | Triggers a `CompletedUnit` event for a full section/module. |
| **Course Progression** | `progressed` | Real-time percentage tracking based on completed tasks / total tasks in Moodle. |
| **Quiz Submitted** | `attempted`| Passes the raw score, minimum passing score, max score, success (`true`/`false`), and completion status. |
| **Course Completed** | `completed` | Triggered when `course_completed` event fires (student reaches 100% or Moodle completion rules met). |
| **Course Rating** | `rated` | Hooks into `tool_courserating`. Fetches raw rating (0-5), scaled rating, and the text review/comment. |
| **Video Watched** | `watched` | Tracks custom events related to video consumption durations. |

## 🧑‍💻 Usage & User Profiling

For the integration to be fully valid according to NELC standards:
1. Ensure the course has **NELC Tracking Enabled**. When editing a course, you will notice a new checkbox `[x] Enable NELC Integration`. Make sure this is ticked.
2. Instructors must specify the **Course Duration** and **Course Language** accurately in the course settings page.
3. Every user must have their **National ID** (`national_id`) filled out. The plugin ensures this displays on signup recursively.

## 🔄 Upgrading

If you are upgrading from an older version of this plugin (e.g. before the auto-national-id implementations):
1. Overwrite the `moodle_lrs_plugin` directory via FTP.
2. Visit **Site administration** → **Notifications**.
3. The plugin’s `upgrade.php` script will seamlessly execute, automatically creating the missing `national_id` user profile field and configuring it for signups without administrative intervention.

## 🛠️ Troubleshooting

- **No connection to LRS / Connection timeout errors:**
  - Verify your Moodle server has outgoing cURL access on the port required by your LRS endpoint.
  - Review your Endpoint string carefully (typically ends with `/xAPI/` or `/statements`).
- **Statements not recording:**
  - Ensure the course setting `is_nelc_enabled` is checked.
  - Make sure the user has a valid `national_id` set in their Moodle profile.
- **cURL HTTP Code warnings inside Moodle:**
  - The plugin leverages `iziToast` to push visible error notifications to the screen. If you get a 401 Unauthorized error in the notification, double check your LRS Basic Auth credentials.

## 📞 Support

For custom integrations, modifications, or enterprise support:

- **WhatsApp:** 
  - [+20 100 094 4804](https://wa.me/201000944804)
  - [+20 106 233 2549](https://wa.me/201062332549)
- **Emails:** 
  - [info@bzzix.com](mailto:info@bzzix.com)
  - [bzzixs@gmail.com](mailto:bzzixs@gmail.com)

## 📄 License

Licensed under the **MIT License**. Custom modifications and enterprise support are available upon request from the developer.

---
**Developed by:** Mohammed Hassan  
**Copyright:** 2025  
**Version:** 2.0.2 (Build 2025121021)