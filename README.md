Yrl Генератор
============
Компонент для генерации фида Яндекс.Недвижимость (<a href="https://help.yandex.ru/webmaster/realty/requirements.xml">Технические требования</a>)

Возможна выгрузка фида прямо в браузер или задать путь для сохранения.<br/>
Для создания фида необходимо создать свой класс для сбора данных и определить в нем метод offers()<br/>

Из массива выстраивается xml дерево в котором ключи массива - теги элемента.<br/>
Для дополнительной обработки некоторых элементов (например metro, image), можно написать хук-метод (writeНазваниеЭлементаElement) в классе компонента. 

Валидатор фида - https://webmaster.yandex.ru/xsdtest.xml

Установка  
============
config/main.php:

    'import'=>array(
        ...
        'application.components.YrlGenerator',
        ...
    ),
    ...
    'components'=>array(
        ...
        'yrlGenerator'=>array(
            'class'=>'MyYrlGenerator',
            // Выгружать в коневую директорию
            'outputFile'=>dirname($_SERVER['SCRIPT_FILENAME']).'/feed.yrl'
        ),
        ...
    )

Ваш компонент:

    class MyYRLGenerator extends YRLGenerator {
      protected function offers() {
        $offers = ...;
        foreach($offers as $offer) {
          $this->addOffer($id,$data);
        }
      }
    }

Запуск генератора:

     $yrlGenerator = Yii::app()->yrlGenerator;
     $yrlGenerator->run();

Подробнее о Яндекс.Недвижимость https://help.yandex.ru/webmaster/realty/quickstart.xml
