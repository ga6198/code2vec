import os
import csv
import math
import pandas as pd
import argparse

def isNaN(num):
    return num != num

def add_to_lookup_dict(lookup_dict, source_file):
    values_to_replace = []

    source_df = pd.read_csv(source_file)

    for row in source_df.itertuples():
        value_id = row[1] #row[0] represents index
        value = row[2]

        #if token/node_type not in lookup_dict, add it
        if value not in lookup_dict:
            lookup_dict[value] = value_id
        else: #if token in lookup_dictionary, retrieve the number. Then compare it against the token id
            id_from_lookup = lookup_dict[value]
            if id_from_lookup != value_id:
                values_to_replaces.append({"original_value":value_id, "new_value":id_from_lookup})

    return lookup_dict, values_to_replace     

#replace the ids in a target file based on passed "values_to_replace" list
def replace_values(target_file, values_to_replace):
    target_df = pd.read_csv(target_file)

    new_data = {'label':[], 'value':[]}

    for row in target_df.itertuples():
        label = ""
        revised_row = "" #holds changed values/second column

        #modify values
        for i, value in enumerate(row):
            if i==1:
                label = value #save the label
            if i!=0 and i!=1: #skip index and label
                if not isNaN(value):
                    individual_values = str(value).split()

                    #Use the values_to_replace to modify the retrieved values from the file
                    for indiv_val in individual_values:
                        for value_pair in values_to_replace:
                            if indiv_val == value_pair["original_value"]:
                                indiv_val = value_pair["new_value"]
                                break

                    #add new values to the revised row
                    new_values = " ".join(individual_values)

                    revised_row = revised_row + new_values

        new_data['label'].append(label)
        new_data['value'].append(revised_row)

    return new_data

def replace_path_contexts(target_file, tokens_to_replace, paths_to_replace):
    #replace the tokens in the path_contexts.csv w/ the ones in the lookup
    path_contexts_df = pd.read_csv(target_file, sep=" ")

    #print(path_contexts_df)
    new_path_contexts_data = {'label': [], 'path-contexts':[]}

    for row in path_contexts_df.itertuples():
        #print(row)
        label = ""
        revised_row = "" #string to hold the changed path-contexts

        #modify the path-contexts
        
        for i, path_context in enumerate(row):
            if i == 1:
                label = path_context #save the label
            if i != 0 and i!= 1: #skip index and label
                #print(path_context)
                if not isNaN(path_context):
                    start_token, path, end_token = str(path_context).split(",")
                    #Use the tokens_to_replace to switch start and end tokens
                    for token_pair in tokens_to_replace:
                        if start_token == token_pair["original_value"]:
                            start_token = token_pair["new_value"]
                            break

                    for path_pair in paths_to_replace:
                        if path == path_pair["original_value"]:
                            path = path_pair["new_value"]
                            break

                    for token_pair in tokens_to_replace:
                        if end_token == token_pair["original_value"]:
                            end_token = token_pair["new_value"]
                            break

                    #add new path_contexts to the revised row
                    new_path_context = start_token + "," + path + "," + end_token

                    #for first path context, don't add a space
                    if i==2:
                        revised_row = revised_row + new_path_context
                    else:
                        revised_row = revised_row + " " + new_path_context

        new_path_contexts_data['label'].append(label)
        new_path_contexts_data['path-contexts'].append(revised_row)

    return new_path_contexts_data

#returns updated lookup dicts
def merge_path_contexts(source_dir, token_lookup, node_lookup, paths_lookup):
    tokens_dir = os.path.join(source_dir, "tokens.csv")
    path_contexts_dir = os.path.join(source_dir, "path_contexts.csv")
    nodes_dir = os.path.join(source_dir, "node_types.csv")
    paths_dir = os.path.join(source_dir, "paths.csv")

    tokens_df = pd.read_csv(tokens_dir)
    print(tokens_df)

    #establish lookup dicts
    token_lookup, tokens_to_replace = add_to_lookup_dict(token_lookup, tokens_dir)

    node_lookup, nodes_to_replace = add_to_lookup_dict(node_lookup, nodes_dir)

    #replace the node_types in paths.csv
    new_paths_data = replace_values(paths_dir, nodes_to_replace)
    print(new_paths_data)

    new_paths_df = pd.DataFrame(new_paths_data, columns = ['label', 'value'])
    new_paths_df.rename(columns={"label":"id", "value":"path"}, inplace=True)
    print(new_paths_df)

    #save paths.csv to file
    output_path = os.path.join(source_dir, "new_paths.csv")
    #save_df_to_file(new_paths_df, output_path, header=True)
    new_paths_df.to_csv(output_path, index=False)

    #once paths are modified, establish a lookup dict for the paths
    paths_lookup, paths_to_replace = add_to_lookup_dict(paths_lookup, paths_dir)

    #officially modifying path contexts
    #replace the tokens and paths in the path_contexts file
    new_path_contexts_data = replace_path_contexts(path_contexts_dir, tokens_to_replace, paths_to_replace)

    #print(new_path_contexts_data)
    new_path_contexts_df = pd.DataFrame(new_path_contexts_data, columns = ['label', 'path-contexts'])
    print(new_path_contexts_df)

    #save path_contexts df to csv
    output_path = os.path.join(source_dir, "new_path_contexts.csv") #output_path = os.path.join(source_dir, "new_path_contexts.csv")
    save_df_to_file(new_path_contexts_df, output_path)

    return token_lookup, node_lookup, paths_lookup

def save_df_to_file(df, output_path, header=False):
    output_string = df.to_string(index=False, header=header)
    output_file = open(output_path, 'w')
    output_file.write(output_string)
    output_file.close()
    strip_extra_spaces(output_path)

def strip_extra_spaces(source_file):
    with open(source_file, 'r') as f:
        lines = f.readlines()

    stripped_lines = []
            
    for line in lines:
        #remove trailing whitespace for label
        line = line.strip()

        #remove additional spaces between label and path-contexts
        line = ' '.join(line.split())

        stripped_lines.append(line)
        #print(line)

    #write lines to file, with newline in between
    with open(source_file, 'w') as f:
        f.write('\n'.join(stripped_lines))
    

#####Main Code#####
parser = argparse.ArgumentParser()
parser.add_argument("-d", "--dir", help="Source directory to parse") #source directory
args = parser.parse_args()

token_lookup = {}
node_lookup = {}
paths_lookup = {}

if args.dir:
    token_lookup, node_lookup, paths_lookup = merge_path_contexts(args.dir, token_lookup, node_lookup, paths_lookup) #merge_path_contexts("pathcontexts", token_lookup)
