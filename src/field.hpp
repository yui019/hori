#ifndef HORI_FIELD_H
#define HORI_FIELD_H

#include <string>
#include <vector>

struct FieldModifiers {
	bool nullable;
	bool auto_increment;
	std::string default_value;
};

enum class FieldIndex {
	PrimaryKey,
	Unique,
	None
};

struct FieldType {
	std::string type_name;
	std::vector<std::string> options;
};

struct Field {
	std::string name;
	FieldType type;
	FieldModifiers modifiers;
	FieldIndex index;
	std::string comment;
};

#endif // HORI_FIELD_H