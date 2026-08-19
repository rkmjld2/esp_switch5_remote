esp_switch5_remote/
│
├── index.php
├── api.php
├── db.php
├── config.php
├── app.ino
├── Dockerfile
├── .gitignore
└── README.md

esp_switch5_remote/
└── config.php


After that we will build:

db.php
api.php
index.php
app.ino
Dockerfile
.gitignore
README.md

esp_switch5_remote/
    index.php      ← I will provide
    api.php        ← provided
    db.php         ← provided
    config.php     ← provided
    app.ino        ← I will provide
    Dockerfile
    .gitignore
    README.md
//////////////////comparison between old esp-switch5 and new espp_switch5_remote 
| Old ESP-SWITCH5                                  | New `esp_switch5_remote`          |
| ------------------------------------------------ | --------------------------------- |
| `esp-switch4-1.onrender.com`                     | `esp-switch5-remote.onrender.com` |
| ESP-SWITCH3/4 comments                           | ESP-SWITCH5 REMOTE                |
| Same Wi-Fi recovery                              | **Preserved**                     |
| Same D1–D8                                       | **Preserved**                     |
| Same 3-second polling                            | **Preserved**                     |
| Same controller ID                               | **Preserved**                     |
| Same device token                                | **Preserved**                     |
| Same BearSSL HTTPS                               | **Preserved**                     |
| Same JSON D1–D8 reading                          | **Preserved**                     |
| Same automatic restart after 2-min Wi-Fi failure | **Preserved**                     |
