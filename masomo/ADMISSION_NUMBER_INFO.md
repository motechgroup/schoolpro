# Admission Number System

## Format

Admission numbers are now **sequential numeric values starting from 100**.

- First student: **100**
- Second student: **101**
- Third student: **102**
- And so on...

## Auto-Generation

When creating a new student:
- If you leave the admission number field **empty**, the system will automatically generate the next sequential number
- The system finds the highest existing admission number and adds 1
- If no students exist, it starts from **100**

## Manual Entry

You can also manually enter an admission number:
- Must be unique (system will check)
- Must be numeric
- Recommended to use sequential numbers for consistency

## Examples

- Student 1: **100**
- Student 2: **101**
- Student 3: **102**
- ...
- Student 50: **149**

## Benefits

- **Simple**: Easy to remember and reference
- **Sequential**: Easy to track and organize
- **No duplicates**: System ensures uniqueness
- **Gap handling**: If a number is deleted, the system will use the next available number

## Difference from UPI

- **Admission Number**: School-specific, sequential (100, 101, 102...)
- **UPI**: National identifier, follows student across schools (UPI2024A1B2C3D4)

Both are automatically generated when creating a student.

