#include "hori.hpp"
#include "src/table.hpp"
#include "src/ui/new_table.hpp"
#include "type_declarations.hpp"

#include <cstddef>
#include <cstdio>
#include <cstring>
#include <raylib.h>

#include <imgui.h>
#include <rlImGui/rlImGui.h>

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

	_tables.push_back(table);

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
		        create_table(_new_table_dialog_state.str_table_name.c_str());

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
	for (auto table : _tables) {
		ImGui::SetNextWindowSizeConstraints(ImVec2(180.0f, 100.0f),
		                                    ImVec2(1000.0f, 1000.0f));

		ImGui::Begin(table.name.c_str());

		if (ImGui::BeginTable(table.name.c_str(), 2)) {
			for (auto field : table.fields) {
				ImGui::TableNextColumn();

				ImGui::Text("%s", field.name.c_str());
				ImGui::TableNextColumn();

				if (field.type.options.empty()) {
					// display just the type name

					ImGui::Text("%s", field.type.type_name.data());
				} else {
					// display a type with options like "VARCHAR (10, 2)"

					std::string options_string = field.type.options[0];
					for (size_t i = 1; i < field.type.options.size(); i++) {
						options_string += ", ";
						options_string += field.type.options[i];
					}

					ImGui::Text("%s (%s)", field.type.type_name.data(),
					            options_string.c_str());
				}
			}

			ImGui::EndTable();
		}

		ImGui::End();
	}
}