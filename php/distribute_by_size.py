import os
import shutil

def distribute(dir_name, split_size):
    #search for all files in the passed dir_name directory
    all_files = []
    for path, subdirs, files in os.walk(dir_name):
        for name in files:
            full_name = os.path.join(path, name)
            print(full_name)
            #only get php files
            if full_name.endswith(".php"):
                all_files.append(full_name)
    
    file_groups = []

    #divide the file groups according to the passed split_size
    max_group_size = split_size
    current_group_size = 0
    current_file_group = []
    for current_file in all_files:
        file_size = os.path.getsize(current_file)
        #if size exceed the split_size, save the current_file_group and reset it
        if file_size + current_group_size > max_group_size:
            file_groups.append(current_file_group)
            #reset group
            current_file_group = []
            current_group_size = 0

            #add the current file
            current_file_group.append(current_file)
            current_group_size += file_size
            
        #if the size does not exceed the split_size, add the file to the current_file_group
        else:
            current_file_group.append(current_file)
            current_group_size += file_size

    for group in file_groups:
        #print(group)
        group_size = 0
        for current_file in group:
            group_size += os.path.getsize(current_file)
        print("Group size (bytes):", group_size)

    #move the files from the groups
    for i, group in enumerate(file_groups):
        target_dir = dir_name + "_" + str(i)
        print("Destination directory", target_dir)
        if not os.path.exists(target_dir):
            os.mkdir(target_dir)
        for current_file in group:
            print("Moving file:", current_file, "to", target_dir) 
            shutil.copy(current_file, target_dir, follow_symlinks=True)
        

    """"
    while higher_index <= max_index:
        files_split = subdirectories[lower_index:higher_index]
        file_groups.append(files_split)
        
        #modify indices to get next group of files
        lower_index = higher_index
        higher_index = higher_index + split_size

        if higher_index > max_index:
            slice_remaining = True

    if slice_remaining == True:
         files_split = subdirectories[lower_index:max_index]
         file_groups.append(files_split)
    """

    #for each subgroup of projects, move to their own file 
    """
    for i, group in enumerate(file_groups):
        target_dir = dir_name + "_" + str(i)
        print(target_dir)
        print(group)
        for project in group:
             source = os.path.join(dir_name, project)
             destination = os.path.join(target_dir, project)
             print(source)
             shutil.copytree(source, destination, symlinks=True)
    """

    print("File distribution completed")



if __name__ == "__main__":
    split_size = 50000000 #in bytes. 50 mb
    distribute("val", split_size)
    distribute("test", split_size)
    distribute("train", split_size)
