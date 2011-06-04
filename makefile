.PHONY: docs clean

docs:
	doxygen doxyfile

clean:
	rm -rf html latex
