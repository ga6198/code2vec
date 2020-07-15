import os
import shutil

def distribute(dir_name, split_size):
    #print(os.listdir(dir_name))
    subdirectories = os.listdir(dir_name)
    
    folder_groups = []

    max_index = len(subdirectories)

    lower_index = 0

    higher_index = split_size
    slice_remaining = False

    while higher_index <= max_index:
        folders_split = subdirectories[lower_index:higher_index]
        folder_groups.append(folders_split)
        
        #modify indices to get next group of folders
        lower_index = higher_index
        higher_index = higher_index + split_size

        if higher_index > max_index:
            slice_remaining = True

    if slice_remaining == True:
         folders_split = subdirectories[lower_index:max_index]
         folder_groups.append(folders_split)

    #for each subgroup of projects, move to their own folder 
    for i, group in enumerate(folder_groups):
        target_dir = dir_name + "_" + str(i)
        print(target_dir)
        print(group)
        for project in group:
             source = os.path.join(dir_name, project)
             destination = os.path.join(target_dir, project)
             print(source)
             shutil.copytree(source, destination, symlinks=True)

    print("File distribution completed")



if __name__ == "__main__":
    split_size = 10
    distribute("val", split_size)
    distribute("test", split_size)
    distribute("train", split_size)
