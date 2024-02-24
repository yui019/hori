#include "raylib.h"

#include "imgui.h"
#include "rlImGui/rlImGui.h"

int main() {
	int screenWidth  = 1280;
	int screenHeight = 800;

	SetConfigFlags(FLAG_WINDOW_RESIZABLE);
	InitWindow(screenWidth, screenHeight, "Hori");
	SetTargetFPS(60);
	rlImGuiSetup(true);

	while (!WindowShouldClose()) {
		BeginDrawing();
		{
			ClearBackground(DARKGRAY);

			rlImGuiBegin();
			{
				bool open = true;
				ImGui::ShowDemoWindow(&open);
			}
			rlImGuiEnd();
		}
		EndDrawing();
	}

	rlImGuiShutdown();
	CloseWindow();

	return 0;
}