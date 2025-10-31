# Terminal Data Module - Setup Commands

## 🚀 Quick Setup - One Liner Commands

### 1. Create Module

```bash
php artisan module:make TerminalData
```

### 2. Create All Migrations

```bash
php artisan module:make-migration create_td_folders_table TerminalData && php artisan module:make-migration create_td_files_table TerminalData && php artisan module:make-migration create_td_shares_table TerminalData && php artisan module:make-migration create_td_activities_table TerminalData && php artisan module:make-migration create_td_tags_table TerminalData && php artisan module:make-migration create_td_taggables_table TerminalData && php artisan module:make-migration create_td_comments_table TerminalData && php artisan module:make-migration create_td_number_counters_table TerminalData && php artisan module:make-migration create_td_file_locks_table TerminalData
```

### 3. Create All Models

```bash
php artisan module:make-model TdFolder TerminalData && php artisan module:make-model TdFile TerminalData && php artisan module:make-model TdShare TerminalData && php artisan module:make-model TdActivity TerminalData && php artisan module:make-model TdTag TerminalData && php artisan module:make-model TdComment TerminalData && php artisan module:make-model TdNumberCounter TerminalData && php artisan module:make-model TdFileLock TerminalData
```

### 4. Create All API Controllers

```bash
php artisan module:make-controller Api/TdFolderController TerminalData --api && php artisan module:make-controller Api/TdFileController TerminalData --api && php artisan module:make-controller Api/TdShareController TerminalData --api && php artisan module:make-controller Api/TdActivityController TerminalData --api && php artisan module:make-controller Api/TdTagController TerminalData --api && php artisan module:make-controller Api/TdCommentController TerminalData --api
```

### 5. Create All Requests

```bash
php artisan module:make-request StoreTdFolderRequest TerminalData && php artisan module:make-request UpdateTdFolderRequest TerminalData && php artisan module:make-request StoreTdFileRequest TerminalData && php artisan module:make-request UpdateTdFileRequest TerminalData && php artisan module:make-request StoreTdShareRequest TerminalData && php artisan module:make-request StoreTdCommentRequest TerminalData
```

### 6. Create All Resources

```bash
php artisan module:make-resource TdFolderResource TerminalData && php artisan module:make-resource TdFileResource TerminalData && php artisan module:make-resource TdShareResource TerminalData && php artisan module:make-resource TdActivityResource TerminalData && php artisan module:make-resource TdTagResource TerminalData && php artisan module:make-resource TdCommentResource TerminalData
```

### 7. Create All Services

```bash
php artisan module:make-class Services/TdFolderService TerminalData && php artisan module:make-class Services/TdFileService TerminalData && php artisan module:make-class Services/TdShareService TerminalData && php artisan module:make-class Services/TdNumberingService TerminalData && php artisan module:make-class Services/TdActivityService TerminalData
```

### 8. Create All Repositories

```bash
php artisan module:make-class Repositories/TdFolderRepository TerminalData && php artisan module:make-class Repositories/TdFileRepository TerminalData && php artisan module:make-class Repositories/TdShareRepository TerminalData
```

### 9. Create All Policies

```bash
php artisan module:make-policy TdFolderPolicy TerminalData && php artisan module:make-policy TdFilePolicy TerminalData && php artisan module:make-policy TdSharePolicy TerminalData
```

### 10. Create All Seeders

```bash
php artisan module:make-seed TdFolderSeeder TerminalData && php artisan module:make-seed TdFileSeeder TerminalData && php artisan module:make-seed TdTagSeeder TerminalData && php artisan module:make-seed TdDatabaseSeeder TerminalData
```

### 11. Create Events & Listeners

```bash
php artisan module:make-event TdFileUploaded TerminalData && php artisan module:make-event TdFileDeleted TerminalData && php artisan module:make-event TdFolderCreated TerminalData && php artisan module:make-listener UpdateFolderStats TerminalData --event=TdFileUploaded && php artisan module:make-listener LogActivity TerminalData --event=TdFileUploaded
```

### 12. Create Tests

```bash
php artisan module:make-test Feature/TdFolderTest TerminalData && php artisan module:make-test Feature/TdFileTest TerminalData && php artisan module:make-test Unit/TdFolderServiceTest TerminalData && php artisan module:make-test Unit/TdFileServiceTest TerminalData
```

---

## 🎯 Complete Setup (Copy & Paste All at Once)

```bash
# Create module
php artisan module:make TerminalData && \

# Migrations
php artisan module:make-migration create_td_folders_table TerminalData && \
php artisan module:make-migration create_td_files_table TerminalData && \
php artisan module:make-migration create_td_shares_table TerminalData && \
php artisan module:make-migration create_td_activities_table TerminalData && \
php artisan module:make-migration create_td_tags_table TerminalData && \
php artisan module:make-migration create_td_taggables_table TerminalData && \
php artisan module:make-migration create_td_comments_table TerminalData && \
php artisan module:make-migration create_td_number_counters_table TerminalData && \
php artisan module:make-migration create_td_file_locks_table TerminalData && \

# Models
php artisan module:make-model TdFolder TerminalData && \
php artisan module:make-model TdFile TerminalData && \
php artisan module:make-model TdShare TerminalData && \
php artisan module:make-model TdActivity TerminalData && \
php artisan module:make-model TdTag TerminalData && \
php artisan module:make-model TdComment TerminalData && \
php artisan module:make-model TdNumberCounter TerminalData && \
php artisan module:make-model TdFileLock TerminalData && \

# API Controllers
php artisan module:make-controller Api/TdFolderController TerminalData --api && \
php artisan module:make-controller Api/TdFileController TerminalData --api && \
php artisan module:make-controller Api/TdShareController TerminalData --api && \
php artisan module:make-controller Api/TdActivityController TerminalData --api && \
php artisan module:make-controller Api/TdTagController TerminalData --api && \
php artisan module:make-controller Api/TdCommentController TerminalData --api && \

# Requests
php artisan module:make-request StoreTdFolderRequest TerminalData && \
php artisan module:make-request UpdateTdFolderRequest TerminalData && \
php artisan module:make-request StoreTdFileRequest TerminalData && \
php artisan module:make-request UpdateTdFileRequest TerminalData && \
php artisan module:make-request StoreTdShareRequest TerminalData && \
php artisan module:make-request StoreTdCommentRequest TerminalData && \

# Resources
php artisan module:make-resource TdFolderResource TerminalData && \
php artisan module:make-resource TdFileResource TerminalData && \
php artisan module:make-resource TdShareResource TerminalData && \
php artisan module:make-resource TdActivityResource TerminalData && \
php artisan module:make-resource TdTagResource TerminalData && \
php artisan module:make-resource TdCommentResource TerminalData && \

# Services
php artisan module:make-class Services/TdFolderService TerminalData && \
php artisan module:make-class Services/TdFileService TerminalData && \
php artisan module:make-class Services/TdShareService TerminalData && \
php artisan module:make-class Services/TdNumberingService TerminalData && \
php artisan module:make-class Services/TdActivityService TerminalData && \

# Repositories
php artisan module:make-class Repositories/TdFolderRepository TerminalData && \
php artisan module:make-class Repositories/TdFileRepository TerminalData && \
php artisan module:make-class Repositories/TdShareRepository TerminalData && \

# Policies
php artisan module:make-policy TdFolderPolicy TerminalData && \
php artisan module:make-policy TdFilePolicy TerminalData && \
php artisan module:make-policy TdSharePolicy TerminalData && \

# Seeders
php artisan module:make-seed TdFolderSeeder TerminalData && \
php artisan module:make-seed TdFileSeeder TerminalData && \
php artisan module:make-seed TdTagSeeder TerminalData && \
php artisan module:make-seed TdDatabaseSeeder TerminalData && \

# Events
php artisan module:make-event TdFileUploaded TerminalData && \
php artisan module:make-event TdFileDeleted TerminalData && \
php artisan module:make-event TdFolderCreated TerminalData && \

# Listeners
php artisan module:make-listener UpdateFolderStats TerminalData --event=TdFileUploaded && \
php artisan module:make-listener LogActivity TerminalData --event=TdFileUploaded && \

# Tests
php artisan module:make-test Feature/TdFolderTest TerminalData && \
php artisan module:make-test Feature/TdFileTest TerminalData && \
php artisan module:make-test Unit/TdFolderServiceTest TerminalData && \
php artisan module:make-test Unit/TdFileServiceTest TerminalData && \

echo "✅ TerminalData Module created successfully!"
```

---

## 🔧 Run Migrations & Seeders

```bash
# Run migrations
php artisan module:migrate TerminalData

# Run seeders
php artisan module:seed TerminalData

# Or migrate with seed
php artisan module:migrate TerminalData --seed
```

---

## 📋 Module Management Commands

```bash
# Enable module
php artisan module:enable TerminalData

# Disable module
php artisan module:disable TerminalData

# List all modules
php artisan module:list

# Publish module
php artisan module:publish TerminalData

# Rollback migration
php artisan module:migrate-rollback TerminalData

# Reset migration
php artisan module:migrate-reset TerminalData

# Refresh migration
php artisan module:migrate-refresh TerminalData

# Refresh with seed
php artisan module:migrate-refresh TerminalData --seed
```

---

## 📁 Generated Structure

```
Modules/TerminalData/
├── Config/
├── Console/
├── Database/
│   ├── Migrations/
│   │   ├── create_td_folders_table.php
│   │   ├── create_td_files_table.php
│   │   ├── create_td_shares_table.php
│   │   ├── create_td_activities_table.php
│   │   ├── create_td_tags_table.php
│   │   ├── create_td_taggables_table.php
│   │   ├── create_td_comments_table.php
│   │   ├── create_td_number_counters_table.php
│   │   └── create_td_file_locks_table.php
│   └── Seeders/
│       ├── TdFolderSeeder.php
│       ├── TdFileSeeder.php
│       ├── TdTagSeeder.php
│       └── TdDatabaseSeeder.php
├── Entities/
│   ├── TdFolder.php
│   ├── TdFile.php
│   ├── TdShare.php
│   ├── TdActivity.php
│   ├── TdTag.php
│   ├── TdComment.php
│   ├── TdNumberCounter.php
│   └── TdFileLock.php
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── TdFolderController.php
│   │       ├── TdFileController.php
│   │       ├── TdShareController.php
│   │       ├── TdActivityController.php
│   │       ├── TdTagController.php
│   │       └── TdCommentController.php
│   ├── Requests/
│   │   ├── StoreTdFolderRequest.php
│   │   ├── UpdateTdFolderRequest.php
│   │   ├── StoreTdFileRequest.php
│   │   ├── UpdateTdFileRequest.php
│   │   ├── StoreTdShareRequest.php
│   │   └── StoreTdCommentRequest.php
│   └── Resources/
│       ├── TdFolderResource.php
│       ├── TdFileResource.php
│       ├── TdShareResource.php
│       ├── TdActivityResource.php
│       ├── TdTagResource.php
│       └── TdCommentResource.php
├── Policies/
│   ├── TdFolderPolicy.php
│   ├── TdFilePolicy.php
│   └── TdSharePolicy.php
├── Repositories/
│   ├── TdFolderRepository.php
│   ├── TdFileRepository.php
│   └── TdShareRepository.php
├── Services/
│   ├── TdFolderService.php
│   ├── TdFileService.php
│   ├── TdShareService.php
│   ├── TdNumberingService.php
│   └── TdActivityService.php
├── Events/
│   ├── TdFileUploaded.php
│   ├── TdFileDeleted.php
│   └── TdFolderCreated.php
├── Listeners/
│   ├── UpdateFolderStats.php
│   └── LogActivity.php
├── Tests/
│   ├── Feature/
│   │   ├── TdFolderTest.php
│   │   └── TdFileTest.php
│   └── Unit/
│       ├── TdFolderServiceTest.php
│       └── TdFileServiceTest.php
└── Routes/
    ├── api.php
    └── web.php
```

---

## 🎯 Next Steps

1. Fill migrations with table schemas
2. Configure models with relationships
3. Implement controllers logic
4. Add validation rules in requests
5. Create service layer business logic
6. Register routes in `Routes/api.php`
7. Register policies in `AuthServiceProvider`
8. Run migrations and seeders
9. Write tests

---

## 📝 Notes

-   All commands use `TerminalData` as module name
-   Prefix `Td` is used for all classes (TdFolder, TdFile, etc.)
-   Database tables use `td_` prefix (td_folders, td_files, etc.)
-   API controllers are in `Api/` namespace
-   Services implement business logic
-   Repositories handle database queries
-   Policies manage authorization

---

## 🚀 Quick Test

```bash
# After setup, test if module is created
php artisan module:list

# Test if migrations are created
php artisan module:migrate TerminalData --pretend
```
