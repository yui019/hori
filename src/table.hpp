#ifndef HORI_TABLE_H
#define HORI_TABLE_H

#include <string>
#include <vector>
#include "field.hpp"

struct TableForeignKeyConstraint {
	std::string field;
	std::string references_table;
	std::string references_field;
};

struct Table {
	std::string name;
	std::vector<Field> fields;
	std::vector<TableForeignKeyConstraint> foreign_keys;
};

#endif // HORI_TABLE_H