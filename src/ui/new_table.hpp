#ifndef UI_NEW_TABLE_H
#define UI_NEW_TABLE_H

#include <string>
#include <imgui.h>
#include <misc/cpp/imgui_stdlib.h>

namespace ui::new_table {

struct State {
	std::string str_table_name;
};

inline void open_popup(State &state) {
	state.str_table_name.clear();
	ImGui::OpenPopup("New table");
}

template <typename F1, typename F2>
void render_popup(State &state, F1 &&on_create, F2 &&on_cancel) {
	// Always center this window when appearing
	ImVec2 center = ImGui::GetMainViewport()->GetCenter();
	ImGui::SetNextWindowPos(center, ImGuiCond_Appearing, ImVec2(0.5f, 0.5f));

	ImGui::SetNextWindowSize(ImVec2(300.0f, 0.0f));

	if (ImGui::BeginPopupModal("New table")) {
		ImGui::Text("Table name:");
		ImGui::InputText("##tableName", &state.str_table_name);

		ImGui::Separator();

		if (ImGui::Button("Create table")) {
			on_create();
		}

		ImGui::SameLine();
		if (ImGui::Button("Cancel")) {
			on_cancel();
		}

		ImGui::EndPopup();
	}
}

} // namespace ui::new_table

#endif // UI_NEW_TABLE_H