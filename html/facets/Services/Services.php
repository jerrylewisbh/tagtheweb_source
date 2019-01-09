<?php
include($_SERVER['DOCUMENT_ROOT']. "/facets/Connection/Connection.php");
class Services
{

    /**
     * Returns the sinlge instance of the connection class.
     * @staticvar Singleton $instance The instance .
     */
    
    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static();
        }
        return $instance;
    }
    
    public function trackPageView($id_session, $referer, $page)
    {
        $sql = "INSERT INTO pageview(id_session,
        referrer,
        page) VALUES (
        :id_session, 
        :referrer, 
        :page) RETURNING id_pageview";
        
        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->bindParam(':id_session', $id_session, PDO::PARAM_STR);
        $stmt->bindParam(':referrer', $referer, PDO::PARAM_STR);
        $stmt->bindParam(':page', $page, PDO::PARAM_STR);
        try {
            $success = $stmt->execute();
            if(!$success){
                print_r($stmt->errorInfo());
            }

        }
        catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage(); 
        }

        return  $stmt->fetch(PDO::FETCH_ASSOC)['id_pageview'];
    }
    
    public function createSession($id_session, $ip, $session_type)
    {
        $result = self::chekcIfSessionExists($id_session);
        //session already exists
        if(sizeof($result) > 0 ){
            return;
        }

        $sql = "INSERT INTO session(id_session,
        ip, id_session_type) VALUES (
        :id_session, 
        :ip,
        :session_type)";
        
        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->bindParam(':id_session', $id_session, PDO::PARAM_STR);
        $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
        $stmt->bindParam(':session_type', $session_type, PDO::PARAM_INT);
        try {
            $success = $stmt->execute();
            if(!$success){
                print_r($stmt->errorInfo());
            }

        }
        catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage(); 
        }
    }


    public function saveAnswers($id_session, $id_test_type, $answers, $started_at, $ended_at)
    {

        $sql = "INSERT INTO test_instance(id_session,
        id_test_type, answers, started_at, ended_at) VALUES (:id_session,
        :id_test_type, 
        :answers, :started_at, :ended_at)";
        
        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->bindParam(':id_session', $id_session, PDO::PARAM_STR);
        $stmt->bindParam(':id_test_type', $id_test_type, PDO::PARAM_INT);
        $stmt->bindParam(':answers', $answers, PDO::PARAM_STR);
        $stmt->bindParam(':started_at', $started_at, PDO::PARAM_STR);
        $stmt->bindParam(':ended_at', $ended_at, PDO::PARAM_STR);
        try {
            $success = $stmt->execute();
            if(!$success){
                print_r($stmt->errorInfo());
            }

        }
        catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage(); 
        }
    }


    public function saveRelevantLinks($id_session, $linkMap)
    {

        $sql = "INSERT INTO relevant_links(id_session, link, relevance) VALUES ";
        $values = '';

        foreach($linkMap as $key=>$value) {
          $values.= "('$id_session', '$key', $value),";
      }

      $sql = $sql.$values;
      $sql = rtrim($sql, ",");
      $stmt = Connection::getInstance()->prepare($sql);

      try {
        $success = $stmt->execute();
        if(!$success){
            print_r($stmt->errorInfo());
        }else{
            return  $success;
        }

    }
    catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage(); 
    }

}




public function saveLinkRanking($id_session, $linkRanking)
{

    $sql = "INSERT INTO link_ranking(id_session, link, rank) VALUES ";
    $values = '';

    foreach($linkRanking as $key=>$value) {
      $values.= "('$id_session',  '$value', $key),";
  }

  $sql = $sql.$values;
  $sql = rtrim($sql, ",");
  $stmt = Connection::getInstance()->prepare($sql);

  try {
    $success = $stmt->execute();
    if(!$success){
        print_r($stmt->errorInfo());
    }else{
        return  $success;
    }

}
catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage(); 
}

}



public function updatePageView($id_pageview, $ended_at, $scroll_percentage)
{

    $sql = "UPDATE pageview SET ended_at = :ended_at, scroll_percentage = :scroll_percentage WHERE id_pageview = :id_pageview";

    $stmt = Connection::getInstance()->prepare($sql);
    $stmt->bindParam(':id_pageview', $id_pageview, PDO::PARAM_INT);
    $stmt->bindParam(':ended_at', $ended_at, PDO::PARAM_STR);
    $stmt->bindParam(':scroll_percentage', $scroll_percentage, PDO::PARAM_STR);
    $success = 0;
    try {
        $success = $stmt->execute();
        if(!$success){
            print_r($stmt->errorInfo());
        }

    }
    catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage(); 
    }
    return $success;
}


public function chekcIfSessionExists($id_session){

    $query =  Connection::getInstance()->prepare("SELECT 1 WHERE EXISTS (SELECT id_session FROM session WHERE id_session = :id)");
    $query ->bindValue(':id',$id_session,  PDO::PARAM_STR);
    $query ->execute();
    return $query ->fetch(PDO::FETCH_NUM)[0];
}

public function getAnswers($id_session, $id_test_type){   
    $query =  Connection::getInstance()->prepare("SELECT * FROM test_instance  WHERE id_session = 
        :id_session AND id_test_type = :id_test_type");

    $query ->bindValue(':id_session',$id_session,  PDO::PARAM_STR);
    $query ->bindValue(':id_test_type',$id_test_type,  PDO::PARAM_INT);
    $query ->execute();
    return $query ->fetch(PDO::FETCH_ASSOC);
}

public function updateSessionEndTime($id_session, $ended_at)
{

    $sql = "UPDATE session SET ended_at = :ended_at WHERE id_session = :id_session";

    $stmt = Connection::getInstance()->prepare($sql);
    $stmt->bindParam(':id_session', $id_session, PDO::PARAM_INT);
    $stmt->bindParam(':ended_at', $ended_at, PDO::PARAM_STR);
    $success = 0;
    try {
        $success = $stmt->execute();
        if(!$success){
            print_r($stmt->errorInfo());
        }

    }
    catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage(); 
    }
    return $success;
}

public function getMinSessionType(){

    $query =  Connection::getInstance()->prepare("SELECT st.id_session_type FROM session_type st 
      LEFT JOIN session s on s.id_session_type = st.id_session_type
      GROUP BY st.id_session_type
      ORDER BY count(s.id_session_type) ,  st.id_session_type
      LIMIT 1");
    $query ->execute();
    return $query ->fetch(PDO::FETCH_NUM)[0];
}

public function getSelectedLinks($session_id){


    $stmt =  Connection::getInstance()->prepare("SELECT id_session, link, max(relevance)
        FROM relevant_links 
        WHERE id_session = :id_session
        GROUP BY id_session, link
        HAVING max(relevance) = 4
        ORDER BY max(relevance) DESC
        LIMIT 15
        ");

    $stmt->bindParam(':id_session', $session_id, PDO::PARAM_STR);
    $stmt ->execute();
    return $stmt ->fetchAll(PDO::FETCH_ASSOC);

}


public function getFacets($page, $minCulture, $maxCulture, $minReligion,
 $maxReligion, $minMatter, $maxMatter, $minLife, $maxLife, $minLaw, 
 $maxLaw, $minIndustry, $maxIndustry, $minGames, $maxGames, $minArts,
  $maxArts, $minSaT, $maxSaT, $minSociety, $maxSociety, $minHumanities, 
 $maxHumanities, $minHealth, $maxHealth, $minRW, $maxRW, $minNature, $maxNature, 
 $minGeo, $maxGeo, $minHistory, $maxHistory, $minPhi, $maxPhi, $minPeople, $maxPeople, $minMath, $maxMath
 ){
    $stmt =  Connection::getInstance()->prepare('SELECT * FROM facets 
        WHERE page = :page
        AND "Culture" >= :minCulture  AND "Culture" <= :maxCulture
        AND "Religion"  >= :minReligion  AND "Religion"  <= :maxReligion
        AND "Matter"  >= :minMatter  AND "Matter"  <= :maxMatter
        AND "Life"  >= :minLife  AND "Life"  <= :maxLife 
        AND "Law"  >= :minLaw  AND "Law"  <= :maxLaw 
        AND "Industry"  >= :minIndustry  AND  "Industry"  <= :maxIndustry 
        AND "Games"  >= :minGames  AND  "Games"  <= :maxGames  
        AND "Arts"  >= :minArts  AND "Arts"  <= :maxArts 
        AND "Science_and_technology"  >= :minSaT  AND "Science_and_technology" <=:maxSaT
        AND "Society"  >= :minSociety  AND "Society"  <= :maxSociety  
        AND "Humanities"  >= :minHumanities  AND "Humanities"  <= :maxHumanities
        AND "Health"  >= :minHealth  AND "Health"  <= :maxHealth 
        AND "Reference_works"  >= :minRW  AND "Reference_works" <=:maxRW
        AND "Nature"  >= :minNature  AND "Nature"  <= :maxNature
        AND "Geography"  >= :minGeo  AND "Geography"  <= :maxGeo 
        AND "History"  >= :minHistory  AND "History"  <= :maxHistory 
        AND "Philosophy"  >= :minPhi  AND "Philosophy"  <= :maxPhi 
        AND "People"  >= :minPeople  AND "People"  <= :maxPeople  
        AND "Mathematics"  >= :minMath  AND "Mathematics"  <= :maxMath');

    $stmt  ->bindValue(':page',$page,  PDO::PARAM_STR);
    $stmt  ->bindValue(':minCulture',$minCulture,  PDO::PARAM_STR);
    $stmt  ->bindValue(':minReligion',$minReligion,  PDO::PARAM_STR);
    $stmt  ->bindValue(':minMatter',$minMatter,  PDO::PARAM_STR);
    $stmt  ->bindValue(':minLife',$minLife,  PDO::PARAM_STR);
    $stmt  ->bindValue(':minLaw',$minLaw,  PDO::PARAM_STR);
    $stmt  ->bindValue(':minIndustry',$minIndustry,  PDO::PARAM_STR);
    $stmt  ->bindValue(':minGames',$minGames,  PDO::PARAM_STR);
    $stmt  ->bindValue(':minArts',$minArts,  PDO::PARAM_STR);
    $stmt  ->bindValue(':minSaT',$minSaT,  PDO::PARAM_STR);
    $stmt  ->bindValue(':minSociety',$minSociety,  PDO::PARAM_STR);
    $stmt  ->bindValue(':minHumanities',$minHumanities,  PDO::PARAM_STR);
    $stmt  ->bindValue(':minHealth',$minHealth,  PDO::PARAM_STR);
    $stmt  ->bindValue(':minRW',$minRW,  PDO::PARAM_STR);
    $stmt  ->bindValue(':minNature',$minNature,  PDO::PARAM_STR);
    $stmt  ->bindValue(':minGeo',$minGeo,  PDO::PARAM_STR);
    $stmt  ->bindValue(':minHistory',$minHistory,  PDO::PARAM_STR);
    $stmt  ->bindValue(':minPhi',$minPhi,  PDO::PARAM_STR);
    $stmt  ->bindValue(':minPeople',$minPeople,  PDO::PARAM_STR);
    $stmt  ->bindValue(':minMath',$minMath,  PDO::PARAM_STR);
    $stmt ->bindValue(':maxCulture',$maxCulture,  PDO::PARAM_STR);
    $stmt  ->bindValue(':maxReligion',$maxReligion,  PDO::PARAM_STR);
    $stmt  ->bindValue(':maxMatter',$maxMatter,  PDO::PARAM_STR);
    $stmt  ->bindValue(':maxLife',$maxLife,  PDO::PARAM_STR);
    $stmt  ->bindValue(':maxLaw',$maxLaw,  PDO::PARAM_STR);
    $stmt  ->bindValue(':maxIndustry',$maxIndustry,  PDO::PARAM_STR);
    $stmt  ->bindValue(':maxGames',$maxGames,  PDO::PARAM_STR);
    $stmt  ->bindValue(':maxArts',$maxArts,  PDO::PARAM_STR);
    $stmt  ->bindValue(':maxSaT',$maxSaT,  PDO::PARAM_STR);
    $stmt  ->bindValue(':maxSociety',$maxSociety,  PDO::PARAM_STR);
    $stmt  ->bindValue(':maxHumanities',$maxHumanities,  PDO::PARAM_STR);
    $stmt  ->bindValue(':maxHealth',$maxHealth,  PDO::PARAM_STR);
    $stmt  ->bindValue(':maxRW',$maxRW,  PDO::PARAM_STR);
    $stmt  ->bindValue(':maxNature',$maxNature,  PDO::PARAM_STR);
    $stmt  ->bindValue(':maxGeo',$maxGeo,  PDO::PARAM_STR);
    $stmt  ->bindValue(':maxHistory',$maxHistory,  PDO::PARAM_STR);
    $stmt  ->bindValue(':maxPhi',$maxPhi,  PDO::PARAM_STR);
    $stmt  ->bindValue(':maxPeople',$maxPeople,  PDO::PARAM_STR);
    $stmt  ->bindValue(':maxMath',$maxMath,  PDO::PARAM_STR);
    $stmt ->execute();
    return $stmt ->fetchAll(PDO::FETCH_ASSOC);

}



}

?>
