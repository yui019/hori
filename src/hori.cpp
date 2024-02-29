#include "hori.hpp"
#include "src/table.hpp"
#include "src/ui/new_table.hpp"
#include "src/ui/table.hpp"
#include "type_declarations.hpp"

#include <cstddef>
#include <cstdio>
#include <cstring>
#include <raylib.h>

#include <imgui.h>
#include <rlImGui/rlImGui.h>
#include <utility>

Hori::Hori() {
	using enum TypeDeclarationOptionType;
	using enum TypeDeclarationOptionRequiredState;

	// clang-format off
	TYPE_DECLARATIONS = {
	    TypeDeclaration{"ID",           {}                                                        },

	    TypeDeclaration{"INT",          {}                                                        },
	    TypeDeclaration{"BIGINT",       {}                                                        },
	    TypeDeclaration{"MEDIUMINT",    {}                                                        },
	    TypeDeclaration{"SMALLINT",     {}                                                        },
	    TypeDeclaration{"TINYINT",      {}                                                        },

        TypeDeclaration{"DECIMAL",      {{"precision", Int, Required}, {"scale", Int, Required}}  },
        TypeDeclaration{"DOUBLE",       {{"precision", Int, Required}, {"scale", Int, Required}}  },
        TypeDeclaration{"FLOAT",        {{"precision", Int, Required}, {"scale", Int, Required}}  },

	    TypeDeclaration{"TEXT",         {}                                                        },
	    TypeDeclaration{"LONGTEXT",     {}                                                        },
	    TypeDeclaration{"MEDIUMTEXT",   {}                                                        },
	    TypeDeclaration{"TINYTEXT",     {}                                                        },

        TypeDeclaration{"DATE",         {}                                                        },
        TypeDeclaration{"TIME",         {{"precision", Int, Optional}}                            },
        TypeDeclaration{"DATETIME",     {{"precision", Int, Optional}}                            },
        TypeDeclaration{"TIMESTAMP",    {{"precision", Int, Optional}}                            },

        TypeDeclaration{"CHAR",         {{"length", Int, Required}}                               },
	    TypeDeclaration{"VARCHAR",      {{"length", Int, Required}}                               },

        TypeDeclaration{"BOOL",         {}                                                        },
        TypeDeclaration{"BINARY",       {}                                                        },
	    TypeDeclaration{"JSON",         {}                                                        },
	    TypeDeclaration{"UUID",         {}                                                        },
	};
	// clang-format on
}

void Hori::render() {
	BeginDrawing();
	{
		ClearBackground(DARKGRAY);

		rlImGuiBegin();

		_render_tables();

		if (_right_click_menu_open) {
			_render_right_click_menu();
		}

		rlImGuiEnd();
	}
	EndDrawing();
}

void Hori::open_right_click_menu(Vector2 position) {
	_right_click_menu_open     = !_right_click_menu_open;
	_right_click_menu_position = position;
}

bool Hori::create_table(const char *name) {
	if (strlen(name) == 0) {
		return false;
	}

	Table table = {};
	table.name  = name;

	_tables.push_back({ui::table::State{}, table});

	return true;
}

void Hori::_render_right_click_menu() {
	ImGui::SetNextWindowPos(
	    ImVec2(_right_click_menu_position.x, _right_click_menu_position.y));

	ImGui::SetNextWindowSize(ImVec2(100.0f, 0.0f));

	ImGui::Begin("Right click menu", &_right_click_menu_open,
	             ImGuiWindowFlags_NoTitleBar | ImGuiWindowFlags_NoMove |
	                 ImGuiWindowFlags_NoResize | ImGuiWindowFlags_NoScrollbar |
	                 ImGuiWindowFlags_NoDecoration);

	// left align
	ImGui::PushStyleVar(ImGuiStyleVar_ButtonTextAlign, ImVec2(0.0f, 0.0f));

	const ImVec2 size = ImVec2(ImGui::GetContentRegionAvail().x, 0.0f);

	if (ImGui::Button("New table", size)) {
		ui::new_table::open_popup(_new_table_dialog_state);
	}

	ImGui::Button("Open file", size);

	ImGui::Button("Refresh", size);

	ImGui::PopStyleVar();

	// has to be rendered within here because it's a popup
	ui::new_table::render_popup(
	    _new_table_dialog_state,
	    [&] {
		    bool success =
		        create_table(_new_table_dialog_state.table_name.c_str());

		    if (success) {
			    ImGui::CloseCurrentPopup();
			    _right_click_menu_open = false;
		    }
	    },
	    [&] {
		    ImGui::CloseCurrentPopup();
		    _right_click_menu_open = false;
	    });

	ImGui::End();
}

void Hori::_render_tables() {
	for (size_t i = 0; i < _tables.size(); i++) {
		ui::table::render(_tables[i].ui_state, _tables[i].data,
		                  TYPE_DECLARATIONS, [&] {});
	}
}