#ifndef HORI_H
#define HORI_H

#include "raylib.h"
#include "src/ui/table.hpp"
#include "ui/new_table.hpp"
#include "table.hpp"
#include "type_declarations.hpp"
#include <utility>
#include <vector>

class Hori {
  private:
	std::vector<TypeDeclaration> TYPE_DECLARATIONS;

	struct TableViewModel {
		ui::table::State ui_state;
		Table data;
	};

	std::vector<TableViewModel> _tables;

	bool _right_click_menu_open        = false;
	Vector2 _right_click_menu_position = Vector2(0, 0);

	ui::new_table::State _new_table_dialog_state;

	void _render_tables();
	void _render_right_click_menu();

  public:
	void render();

	void open_right_click_menu(Vector2 position);

	// returns false if it failed to create a table
	bool create_table(const char *name);

	Hori();
};

#endif // HORI_H