#ifndef UI_TABLE_H
#define UI_TABLE_H

#include "src/table.hpp"
#include "src/type_declarations.hpp"
#include <cstddef>
#include <string>
#include <imgui.h>
#include <misc/cpp/imgui_stdlib.h>
#include <vector>

namespace ui::table {

struct State {
	std::string field_name;
	size_t field_type = 0;
};

template <typename F1>
void render(State &state, Table table,
            std::vector<TypeDeclaration> type_declarations,
            F1 &&on_create_field) {
	ImGui::SetNextWindowSizeConstraints(ImVec2(180.0f, 100.0f),
	                                    ImVec2(1000.0f, 1000.0f));

	ImGui::Begin(table.name.c_str());

	// -----------

	ImGui::InputText("##fieldName", &state.field_name);

	const char *combo_label = type_declarations[state.field_type].name;

	if (ImGui::BeginCombo("##combo", combo_label)) {
		for (size_t i = 0; i < type_declarations.size(); i++) {
			bool is_selected = (state.field_type == i);

			if (ImGui::Selectable(type_declarations[i].name, is_selected)) {
				state.field_type = i;
			}

			if (is_selected) {
				ImGui::SetItemDefaultFocus();
			}
		}
		ImGui::EndCombo();
	}

	if (ImGui::Button("Create")) {
		on_create_field();
	}

	ImGui::Separator();

	// -----------

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

} // namespace ui::table

#endif // UI_TABLE_H