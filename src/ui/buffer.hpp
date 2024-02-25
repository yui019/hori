#ifndef UI_BUFFER_H
#define UI_BUFFER_H

#include <cstddef>
#include <cstring>
#include <string>

template <size_t N> struct Buffer {
	char data[N] = {0};
	size_t size  = N;

	void clear() {
		memset(data, 0, N);
	}
};

#endif // UI_BUFFER_H
