import os
import sys
import uuid

#at the end, clean up the dictionaries to remove different ids representing the same value. Then replace those removed ids in the path_contexts

def compile_dictionaries(file_dir, tokens_dict, paths_dict, nodes_dict):
    tokens_csv = os.path.join(file_dir, "tokens.csv")
    paths_csv = os.path.join(file_dir, "paths.csv")
    nodes_csv = os.path.join(file_dir, "node_types.csv")

    tokens_dict, token_ids_to_replace = add_to_dict(tokens_csv, tokens_dict)
    nodes_dict, node_ids_to_replace = add_to_dict(nodes_csv, nodes_dict)

def add_to_dict(file_dir, target_dict):
    lines = tuple(open(file_dir, "r"))
    duplicate_ids = []
    current_dict = target_dict
    
    for line in lines:
        value_id, value = line.split(",")
        if value_id not in target_dict:
            current_dict[value_id] = value
        else: #if the id exists in the dict already, a new id must be assigned. Can use a uuid
            current_dict[uuid.uuid4()] = value
            #add the duplicate id to the ids that need to be replaced
            duplicate_ids.append(value_id)

    return current_dict, duplicate_ids
    

def retrieve_path_contexts(file_dir):
    path_contexts_csv = os.path.join(file_dir, "path_contexts.csv")
    with open(path_contexts_csv, "r") as f:
        return f.read()

if __name__=="__main__":
    target_dir = sys.argv[1] #e.g. php/result/test/php
    """
    for root, dirs, files in os.walk(target_dir):
        for name in dirs:
            full_file_dir = os.path.join(root, name)
            print(full_file_dir)
    """
    #grab subfolders: e.g. test_0, test_1, test_2
    subfolders = [f.path for f in os.scandir(target_dir) if f.is_dir()]
    print(subfolders)

    tokens_dict = []
    paths_dict = []
    nodes_dict = []

    for folder in subfolders:
        target_folder = os.path.join(folder, "php")
        compile_dictionaries(target_folder, tokens_dict, paths_dict, nodes_dict)

