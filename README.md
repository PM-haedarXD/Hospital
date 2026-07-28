<div align="center">

<h1>🏥 Hospital</h1>

<p><em>Realistic hospital system for PocketMine-MP servers</em></p>

<p>
  <img src="https://img.shields.io/badge/PocketMine--MP-5.0.0-fb8c00?style=for-the-badge&logo=github" alt="API">
  <img src="https://img.shields.io/badge/version-1.0.0-blue?style=for-the-badge" alt="Version">
  <img src="https://img.shields.io/badge/license-MIT-green?style=for-the-badge" alt="License">
  <img src="https://img.shields.io/badge/PHP-8.0%2B-777bb3?style=for-the-badge&logo=php" alt="PHP">
</p>

</div>

---

## 📖 Overview

> **Hospital** adds a realistic death-respawn-hospital system. Players respawn in a bed, rest for 10 seconds, then recover in hospital with effects and countdown before release.

| Feature | Description |
|--------|------------|
| 🛏️ **Bed Respawn** | Players respawn on configured bed |
| ⏳ **50s Recovery** | Countdown with progress bar |
| 💊 **Medical Effects** | Nausea + Slowness + Blindness |
| 🔊 **Sound Alerts** | Beep sound in last 10 seconds |
| 🚫 **Anti-Escape** | Cannot leave hospital area |
| 🎨 **Title UI** | Beautiful progress bar display |

---

## 🧬 Core Methods

<strong>onCommand():</strong> Sets hospital location with /sethospital and bed with /setbedh (click on bed)

<strong>onRespawn():</strong> Sets respawn point to bed, makes player sleep 10s, then sends to hospital

<strong>sendToHospital():</strong> Teleports to hospital, applies 3 medical effects, starts 50s countdown

<strong>getProgressBar():</strong> Creates visual progress bar with green/gray blocks

<strong>release():</strong> Cancels all effects and timer, sends player to lobby

<strong>onMove():</strong> Prevents player from leaving 10-block radius around hospital

---

## 📥 Installation

| Step | Action |
|:---:|--------|
| 1 | Download Hospital.phar from Releases |
| 2 | Place in plugins/ folder |
| 3 | Restart server |
| 4 | Set hospital: /sethospital |
| 5 | Set bed: /setbedh then click a bed |

---

## 🔧 Commands & Permissions

| Command | Permission | Default |
|---------|-----------|:-------:|
| /sethospital | hospital.admin | OP |
| /setbedh | hospital.admin | OP |

---

## ⚙️ Config

<pre>
hospital_location:
  x: 0
  y: 64
  z: 0
  world: world

bed_location:
  x: 10
  y: 64
  z: 5
  world: world
</pre>

---

## 📁 Project Structure

<pre>
Hospital/
├── plugin.yml
├── resources/
│   └── config.yml
└── src/
    └── haedarXD/
        └── Hospital/
            └── Hospital.php
</pre>

---

## 👤 Author

<div align="center">

<img src="https://github.com/PM-haedarXD.png" width="80" style="border-radius: 50%;">

### haedarXD

<a href="https://github.com/PM-haedarXD"><img src="https://img.shields.io/badge/GitHub-PM--haedarXD-24292e?style=flat-square&logo=github" alt="GitHub"></a>

</div>

---

## 📜 License

MIT — Free to use, modify, and distribute.

---
