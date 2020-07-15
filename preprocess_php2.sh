#!/usr/bin/env bash
###########################################################
# Change the following values to preprocess a new dataset.
# TRAIN_DIR, VAL_DIR and TEST_DIR should be paths to      
#   directories containing sub-directories with .java files
#   each of {TRAIN_DIR, VAL_DIR and TEST_DIR} should have sub-dirs,
#   and data will be extracted from .java files found in those sub-dirs).
# DATASET_NAME is just a name for the currently extracted 
#   dataset.                                              
# MAX_CONTEXTS is the number of contexts to keep for each 
#   method (by default 200).                              
# WORD_VOCAB_SIZE, PATH_VOCAB_SIZE, TARGET_VOCAB_SIZE -   
#   - the number of words, paths and target words to keep 
#   in the vocabulary (the top occurring words and paths will be kept). 
#   The default values are reasonable for a Tesla K80 GPU 
#   and newer (12 GB of board memory).
# NUM_THREADS - the number of parallel threads to use. It is 
#   recommended to use a multi-core machine for the preprocessing 
#   step and set this value to the number of cores.
# PYTHON - python3 interpreter alias.
TRAIN_DIR=php/train
VAL_DIR=php/val
TEST_DIR=php/test
DATASET_NAME=sqlInjection
MAX_CONTEXTS=200
WORD_VOCAB_SIZE=1301136
PATH_VOCAB_SIZE=911417
TARGET_VOCAB_SIZE=261245
NUM_THREADS=8 #2 #64
PYTHON=python3
###########################################################

TRAIN_DATA_FILE=php/result/train/php/path_contexts.csv #${DATASET_NAME}.train.raw.txt
VAL_DATA_FILE=php/result/val/php/path_contexts.csv #${DATASET_NAME}.val.raw.txt
TEST_DATA_FILE=php/result/test/php/path_contexts.csv #${DATASET_NAME}.test.raw.txt
EXTRACTOR_JAR=cli-0.3.jar #JavaExtractor/JPredict/target/JavaExtractor-0.0.1-SNAPSHOT.jar

mkdir -p data
mkdir -p data/${DATASET_NAME}

echo "Extracting paths from validation set..."
#${PYTHON} JavaExtractor/extract.py --dir ${VAL_DIR} --max_path_length 8 --max_path_width 2 --num_threads ${NUM_THREADS} --jar ${EXTRACTOR_JAR} > ${VAL_DATA_FILE}
#java -Xms24G -jar ${EXTRACTOR_JAR} code2vec --lang php --project php/val --output php/result/val
#java -Xmx24G -jar ${EXTRACTOR_JAR} code2vec --lang php --project php/val --output php/result/val --batchMode --batchSize 100 # --maxContexts 500 --maxPaths 400 # --batchMode --batchSize 100 

for path in php/val_*
do
    echo ${path}
    #outputdir="php/result/${path}"
    #echo ${outputdir}
    java -Xmx24G -jar ${EXTRACTOR_JAR} code2vec --lang php --project ${path} --output php/result/val/${path}
done

#merge csv files to the main path_contexts.csv file
for path in php/result/val/php/*/php/path_contexts*
do
    echo ${path}
    cat ${path} >> php/result/val/php/path_contexts.csv
done

echo "Finished extracting paths from validation set"

#read -rsp $'Press any key to continue...\n' -n1 key

echo "Extracting paths from test set..."
#${PYTHON} JavaExtractor/extract.py --dir ${TEST_DIR} --max_path_length 8 --max_path_width 2 --num_threads ${NUM_THREADS} --jar ${EXTRACTOR_JAR} > ${TEST_DATA_FILE}
#java -Xms24G -jar ${EXTRACTOR_JAR} code2vec --lang php --project php/test --output php/result/test

for path in php/test_*
do
    echo ${path}
    #outputdir="php/result/${path}"
    #echo ${outputdir}
    java -Xmx24G -jar ${EXTRACTOR_JAR} code2vec --lang php --project ${path} --output php/result/test/${path}
done

#merge csv files to the main path_contexts.csv file
for path in php/result/test/php/*/php/path_contexts*
do
    echo ${path}
    cat ${path} >> php/result/test/php/path_contexts.csv
done

echo "Finished extracting paths from test set"

#read -rsp $'Press any key to continue...\n' -n1 key

echo "Extracting paths from training set..."
#${PYTHON} JavaExtractor/extract.py --dir ${TRAIN_DIR} --max_path_length 8 --max_path_width 2 --num_threads ${NUM_THREADS} --jar ${EXTRACTOR_JAR} | shuf > ${TRAIN_DATA_FILE}
#java -Xms24G -jar ${EXTRACTOR_JAR} code2vec --lang php --project php/train --output php/result/train

for path in php/train_*
do
    echo ${path}
    #outputdir="php/result/${path}"
    #echo ${outputdir}
    java -Xmx24G -jar ${EXTRACTOR_JAR} code2vec --lang php --project ${path} --output php/result/train/${path}
done

#merge csv files to the main path_contexts.csv file
for path in php/result/train/php/*/php/path_contexts*
do
    echo ${path}
    cat ${path} >> php/result/train/php/path_contexts.csv
done

echo "Finished extracting paths from training set"

#read -rsp $'Press any key to continue...\n' -n1 key

TARGET_HISTOGRAM_FILE=data/${DATASET_NAME}/${DATASET_NAME}.histo.tgt.c2v
ORIGIN_HISTOGRAM_FILE=data/${DATASET_NAME}/${DATASET_NAME}.histo.ori.c2v
PATH_HISTOGRAM_FILE=data/${DATASET_NAME}/${DATASET_NAME}.histo.path.c2v

echo "Creating histograms from the training data"
cat ${TRAIN_DATA_FILE} | cut -d' ' -f1 | awk '{n[$0]++} END {for (i in n) print i,n[i]}' > ${TARGET_HISTOGRAM_FILE}
cat ${TRAIN_DATA_FILE} | cut -d' ' -f2- | tr ' ' '\n' | cut -d',' -f1,3 | tr ',' '\n' | awk '{n[$0]++} END {for (i in n) print i,n[i]}' > ${ORIGIN_HISTOGRAM_FILE}
cat ${TRAIN_DATA_FILE} | cut -d' ' -f2- | tr ' ' '\n' | cut -d',' -f2 | awk '{n[$0]++} END {for (i in n) print i,n[i]}' > ${PATH_HISTOGRAM_FILE}

# print out information on data files to detect any issues before preprocessing
echo "Train data raw file size:"
stat --printf="%s\n" ${TRAIN_DATA_FILE}
echo "Val data raw file size:"
stat --printf="%s\n" ${VAL_DATA_FILE}
echo "Test data raw file size:"
stat --printf="%s\n" ${TEST_DATA_FILE}

${PYTHON} preprocess.py --train_data ${TRAIN_DATA_FILE} --test_data ${TEST_DATA_FILE} --val_data ${VAL_DATA_FILE} \
  --max_contexts ${MAX_CONTEXTS} --word_vocab_size ${WORD_VOCAB_SIZE} --path_vocab_size ${PATH_VOCAB_SIZE} \
  --target_vocab_size ${TARGET_VOCAB_SIZE} --word_histogram ${ORIGIN_HISTOGRAM_FILE} \
  --path_histogram ${PATH_HISTOGRAM_FILE} --target_histogram ${TARGET_HISTOGRAM_FILE} --output_name data/${DATASET_NAME}/${DATASET_NAME}
    
# If all went well, the raw data files can be deleted, because preprocess.py creates new files 
# with truncated and padded number of paths for each example.
# rm ${TRAIN_DATA_FILE} ${VAL_DATA_FILE} ${TEST_DATA_FILE} ${TARGET_HISTOGRAM_FILE} ${ORIGIN_HISTOGRAM_FILE} \
  ${PATH_HISTOGRAM_FILE}

