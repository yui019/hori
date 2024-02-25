#include "raylib.h"

#include "imgui.h"
#include "rlImGui/rlImGui.h"
#include "hori.hpp"

int main() {
	int screenWidth  = 1280;
	int screenHeight = 800;

	SetConfigFlags(FLAG_WINDOW_RESIZABLE);
	InitWindow(screenWidth, screenHeight, "Hori");
	SetTargetFPS(60);
	rlImGuiSetup(true);

	Hori hori;

	while (!WindowShouldClose()) {
		if (IsMouseButtonPressed(MOUSE_BUTTON_RIGHT)) {
			const Vector2 pos = GetMousePosition();
			hori.open_right_click_menu(pos);
		}

		hori.render();
	}

	rlImGuiShutdown();
	CloseWindow();

	return 0;
}