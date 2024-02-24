project('Hori', 'cpp',
  default_options : ['warning_level=3', 'cpp_std=c++20'])

cxx = meson.get_compiler('cpp')

subdir('src')

inc = include_directories('include')

cmake = import('cmake')

raylib_opts = cmake.subproject_options()
raylib_opts.set_install(false)
raylib_opts.add_cmake_defines({
  'BUILD_EXAMPLES': 'OFF',
})
raylib_proj = cmake.subproject('raylib', options: raylib_opts)
raylib_dep = raylib_proj.dependency('raylib')

imgui_dep = dependency('imgui')

deps = [
  raylib_dep,
  imgui_dep
]

if host_machine.system() == 'windows'
  deps += [
    cxx.find_library('winmm'),
  ]
elif host_machine.system() == 'linux'
  deps += [
    cxx.find_library('m'),
    cxx.find_library('dl'),
  ]
endif


executable('hori',
  sources: src,
  include_directories: inc,
  dependencies: deps
)
