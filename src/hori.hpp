#ifndef HORI_H
#define HORI_H

#include "raylib.h"
#include "ui/new_table.hpp"
#include "table.hpp"
#include "type_declarations.hpp"
#include <vector>

class Hori {
  private:
	std::vector<TypeDeclaration> TYPE_DECLARATIONS;

	std::vector<Table> _tables;

	bool _right_click_menu_open        = false;
	Vector2 _right_click_menu_position = Vector2(0, 0);

	bool _new_table_dialog_open = true;
	UiStateNewTable _new_table_dialog_state;

	void _render_tables();
	void _render_right_click_menu();
	void _render_new_table_dialog();

  public:
	void render();

	void open_right_click_menu(Vector2 position);
	void open_new_table_dialog();

	// returns false if it failed to create a table
	bool create_table(const char *name);

	Hori();
};

#endif // HORI_H