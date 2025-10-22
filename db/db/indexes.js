/**
 * MongoDB Index Creation Script for Task Management System
 *
 * This script creates optimal indexes for the tasks collection based on
 * the query patterns used in the application.
 *
 * To run this script, use mongosh:
 * mongosh < indexes.js
 *
 * Or connect to your database and run each command individually.
 */

// Connect to your database (adjust the database name as needed)
db = db.getSiblingDB('task_management');

// Drop existing indexes (except _id) if you want to recreate them
db.tasks.dropIndexes();

print("Creating indexes for tasks collection...");

// 1. Single-field index on 'status' for filtering tasks by status
// Used in queries: GET /tasks?status=pending
db.tasks.createIndex(
  { status: 1 },
  {
    name: "idx_status",
    background: true
  }
);
print("✓ Created index: idx_status");

// 2. Single-field index on 'priority' for filtering tasks by priority
// Used in queries: GET /tasks?priority=high
db.tasks.createIndex(
  { priority: 1 },
  {
    name: "idx_priority",
    background: true
  }
);
print("✓ Created index: idx_priority");

// 3. Single-field index on 'due_date' for filtering and sorting by due date
// Used in queries: GET /tasks?due_date_from=2024-01-01&sort_by=due_date
db.tasks.createIndex(
  { due_date: 1 },
  {
    name: "idx_due_date",
    background: true
  }
);
print("✓ Created index: idx_due_date");

// 4. Single-field index on 'created_at' for default sorting (most common)
// Used in queries: GET /tasks (default sorting by created_at DESC)
db.tasks.createIndex(
  { created_at: -1 },
  {
    name: "idx_created_at_desc",
    background: true
  }
);
print("✓ Created index: idx_created_at_desc");

// 5. Compound index on 'title' and 'description' for LIKE-based search
// Used in queries: GET /tasks?search=keyword
// Note: Since the app uses LIKE queries (not $text), we use regular indexes
db.tasks.createIndex(
  { title: 1 },
  {
    name: "idx_title",
    background: true
  }
);
print("✓ Created index: idx_title");

db.tasks.createIndex(
  { description: 1 },
  {
    name: "idx_description",
    background: true
  }
);
print("✓ Created index: idx_description");

// 6. Index on 'updated_at' for sorting by last update
// Used in queries: GET /tasks?sort_by=updated_at
db.tasks.createIndex(
  { updated_at: -1 },
  {
    name: "idx_updated_at_desc",
    background: true
  }
);
print("✓ Created index: idx_updated_at_desc");

// 7. Compound index for common filter combinations
// Used in queries: GET /tasks?status=pending&priority=high
db.tasks.createIndex(
  {
    status: 1,
    priority: 1,
    created_at: -1  // Added default sort
  },
  {
    name: "idx_status_priority_created",
    background: true
  }
);
print("✓ Created index: idx_status_priority_created");

// 8. Compound index for status with sorting by due_date
// Used in queries: GET /tasks?status=pending&sort_by=due_date
db.tasks.createIndex(
  {
    status: 1,
    due_date: 1
  },
  {
    name: "idx_status_due_date",
    background: true
  }
);
print("✓ Created index: idx_status_due_date");

// 9. Compound index for priority with sorting by created_at
// Used in queries: GET /tasks?priority=high&sort_by=created_at
db.tasks.createIndex(
  {
    priority: 1,
    created_at: -1
  },
  {
    name: "idx_priority_created_at",
    background: true
  }
);
print("✓ Created index: idx_priority_created_at");

// 10. Compound index for status with sorting by priority and created_at
// Used in queries: GET /tasks?status=pending&sort_by=priority
db.tasks.createIndex(
  {
    status: 1,
    priority: 1,
    created_at: -1
  },
  {
    name: "idx_status_priority_sort",
    background: true
  }
);
print("✓ Created index: idx_status_priority_sort");

// 11. Compound index for due_date range queries with sorting
// Used in queries: GET /tasks?due_date_from=X&due_date_to=Y&sort_by=due_date
db.tasks.createIndex(
  {
    due_date: 1,
    created_at: -1
  },
  {
    name: "idx_due_date_created",
    background: true
  }
);
print("✓ Created index: idx_due_date_created");

print("\n=== Index Creation Complete ===\n");

// List all indexes to verify
print("Current indexes on tasks collection:");
db.tasks.getIndexes().forEach(function(index) {
  print("  - " + index.name + ": " + JSON.stringify(index.key));
});

print("\n=== Index Rationale ===");
print("\n1. Single-field indexes:");
print("   - status, priority: Support equality filters");
print("   - due_date: Support range queries (>=, <=) and sorting");
print("   - created_at, updated_at: Support default and custom sorting");
print("   - title, description: Support LIKE-based search queries");
print("\n2. Compound indexes (following ESR - Equality, Sort, Range):");
print("   - idx_status_priority_created: Status + Priority filters with default sort");
print("   - idx_status_due_date: Status filter with due_date sorting");
print("   - idx_priority_created_at: Priority filter with created_at sorting");
print("   - idx_status_priority_sort: Status filter with priority sorting");
print("   - idx_due_date_created: Due date range queries with default sort");
print("\n3. Index Strategy:");
print("   - Background: true - indexes created without blocking operations");
print("   - ESR rule followed: Equality fields first, then Sort fields, then Range fields");
print("   - Aligned with actual query patterns in TaskController");
print("   - Supports all sortable fields: id, title, description, status, priority,");
print("     due_date, created_at, updated_at");
print("\n4. Key Changes from Previous Version:");
print("   - Removed MongoDB text index (app uses LIKE queries, not $text)");
print("   - Added title and description regular indexes for LIKE support");
print("   - Added updated_at index for sorting support");
print("   - Optimized compound indexes for actual query patterns");
print("   - Added due_date range query optimization");
print("\nNote: Monitor index usage with db.tasks.aggregate([{$indexStats:{}}])");
print("and remove unused indexes to optimize storage and write performance.");
