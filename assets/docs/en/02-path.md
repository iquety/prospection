# Path

--page-nav--

## Functionalities

When building a new `Path` object, it is necessary to provide a **context**, that is, a valid directory that will serve as a limit for manipulating paths.

In other words, only file and directory nodes that are inside the context directory can be accessed by the `Path` instance.

If the context is the path to a file, the context will be ignored and the manipulation will work assuming the file path is safe.

The available methods are:

- addNodePath
- getAbsolutePath
- getContextPath
- getDirectory
- getExtension
- getFile
- getName
- getNodePath
- getPath
- isLocalPath
- isRelativePath

--page-nav--
