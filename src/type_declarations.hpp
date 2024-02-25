#ifndef HORI_TYPE_DECLARATIONS_H
#define HORI_TYPE_DECLARATIONS_H

#include <string>
#include <sys/types.h>
#include <vector>

enum class TypeDeclarationOptionType {
	String,
	Int,
	Bool
};

enum class TypeDeclarationOptionRequiredState {
	Required,
	Optional
};

struct TypeDeclarationOption {
	const char *name;
	TypeDeclarationOptionType type;
	TypeDeclarationOptionRequiredState required_state;
};

struct TypeDeclaration {
	const char *name;
	std::vector<TypeDeclarationOption> options;
};

#endif // HORI_TYPE_DECLARATIONS_H